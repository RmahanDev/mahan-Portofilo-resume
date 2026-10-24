<?php
// Single portfolio item, served at /Projects/{slug} (see .htaccess).
$GLOBALS['ROOT_DEPTH'] = 1;
require __DIR__ . '/app/bootstrap.php';

$prefix = '../';
$slug = (string) ($_GET['slug'] ?? '');
$project = row('SELECT * FROM projects WHERE slug = ? AND is_published = 1', [$slug]);
if (!$project) {
    not_found($prefix);
}
$images = rows('SELECT * FROM project_images WHERE project_id = ? ORDER BY sort, id', [$project['id']]);

$active = 'portfolio';
$title = $project['meta_title'] !== '' ? $project['meta_title'] : $project['title'];
$description = $project['meta_desc'] !== '' ? $project['meta_desc'] : meta_text($project['body_html']);
$scrollLine = true;

$titleSuffix = true;
$canonicalPath = 'Projects/' . rawurlencode($project['slug']);
$projectUrl = url_for($canonicalPath);
$image = $project['header_image'] !== '' ? $project['header_image'] : $project['cover'];
if ($image !== '') {
    $socialImage = absolute_url($image);
    $socialImageAlt = $project['title'];
}
$created = jalali_text_to_iso($project['date_text']);
$person = ['@id' => site_url() . '/#person'];
$work = [
    '@type'            => 'CreativeWork',
    '@id'              => $projectUrl . '#work',
    'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $projectUrl],
    'name'             => $project['title'],
    'description'      => $description,
    'url'              => $projectUrl,
    'image'            => $image !== '' ? [$socialImage] : null,
    'dateCreated'      => $created,
    'dateModified'     => iso_datetime($project['updated_at']),
    'genre'            => $project['category_label'] !== '' ? $project['category_label'] : null,
    'inLanguage'       => 'fa-IR',
    'creator'          => $person,
    'author'           => $person,
];
// A free plugin with a download box is described as software, which is what people search for.
if (preg_match('~href="(assets/downloads/[^"]+)"~', $project['body_html'], $download)) {
    $work['@type'] = 'SoftwareApplication';
    $work['applicationCategory'] = 'WebApplication';
    $work['operatingSystem'] = 'WordPress';
    $work['downloadUrl'] = absolute_url($download[1]);
    $work['isAccessibleForFree'] = true;
    $work['license'] = 'https://www.gnu.org/licenses/gpl-2.0.html';
    $work['offers'] = ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'IRR', 'availability' => 'https://schema.org/InStock'];
    if (preg_match('~(\d+\.\d+(?:\.\d+)?)~', strip_tags((string) preg_replace('~^.*?download-box__meta~s', '', $project['body_html'])), $ver)) {
        $work['softwareVersion'] = $ver[1];
    }
}
$schema = [
    array_filter($work, static fn($v) => $v !== null),
    ['@id' => $projectUrl . '#breadcrumb'] + schema_breadcrumb([
        [setting('profile_name'), site_url() . '/'],
        [setting('nav_portfolio'), url_for('portfolio')],
        [$project['title'], $projectUrl],
    ]),
];

ob_start();
require __DIR__ . '/app/views/photoswipe.php';
$beforeTop = ob_get_clean();

require __DIR__ . '/app/views/layout_top.php';
?>

                        <div class="pb-3">
                            <h2 class="title title--h1 title__separate"><?= s('project_page_title') ?></h2>
                        </div>

                        <a class="btn-back" href="../portfolio"><i class="feathericon-arrow-right"></i><?= s('portfolio_back_text') ?></a>

                        <header class="header-project">
                            <h1 class="title title--h1"><?= $project['heading_html'] ?></h1>
<?php if ($project['header_image'] !== ''): ?>
                            <div class="header-project__image-wrap">
                                <img class="cover lazyload" src="<?= asset($project['header_image'], $prefix) ?>" alt="<?= e($project['title']) ?>" fetchpriority="high" />
                            </div>
<?php endif; ?>
                        </header>

<?php if ($project['client'] !== '' || $project['category_label'] !== '' || $project['date_text'] !== ''): ?>
                        <ul class="details-info details-info--inline">
<?php if ($project['client'] !== ''): ?>
                            <li class="details-info__item">
                                <span class="box icon-box"><i class="font-icon feathericon-user"></i></span>
                                <div class="details-info__info">
                                    <span class="overhead">مشتری</span>
                                    <?= e($project['client']) ?>
                                </div>
                            </li>
<?php endif; ?>
<?php if ($project['category_label'] !== ''): ?>
                            <li class="details-info__item">
                                <span class="box icon-box"><i class="font-icon feathericon-layers"></i></span>
                                <div class="details-info__info">
                                    <span class="overhead">دسته بندی</span>
                                    <?= e($project['category_label']) ?>
                                </div>
                            </li>
<?php endif; ?>
<?php if ($project['date_text'] !== ''): ?>
                            <li class="details-info__item">
                                <span class="box icon-box"><i class="font-icon icon-calendar"></i></span>
                                <div class="details-info__info">
                                    <span class="overhead">تاریخ</span>
                                    <?= e($project['date_text']) ?>
                                </div>
                            </li>
<?php endif; ?>
                        </ul>
<?php endif; ?>

                        <?= rich($project['body_html'], $prefix) ?>

<?php if ($images): ?>
                        <div class="swiper-container js-carousel-project">
                            <div class="swiper-wrapper project-gallery">
<?php foreach ($images as $i => $img): ?>
                                <figure class="swiper-slide swiper-slide-project">
                                    <a <?= $i === 0 ? 'id="first" ' : '' ?>title="click to zoom-in"
                                        href="<?= asset($img['path'], $prefix) ?>" data-size="<?= (int) $img['width'] ?>x<?= (int) $img['height'] ?>">
                                        <img class="lazyload" src="<?= asset($img['path'], $prefix) ?>" alt="<?= e($project['title'] . ' - تصویر ' . fa_digits($i + 1)) ?>" width="<?= (int) $img['width'] ?>" height="<?= (int) $img['height'] ?>"<?= $i > 0 ? ' loading="lazy" decoding="async"' : '' ?> />
                                    </a>
                                </figure>
<?php endforeach; ?>
                            </div>
                            <div class="swiper-pagination"></div>
                        </div>
<?php endif; ?>
<?php require __DIR__ . '/app/views/layout_bottom.php';
