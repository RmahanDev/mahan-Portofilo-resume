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
$description = $project['meta_desc'];
$scrollLine = true;

ob_start();
require __DIR__ . '/app/views/photoswipe.php';
$beforeTop = ob_get_clean();

require __DIR__ . '/app/views/layout_top.php';
?>

                        <div class="pb-3">
                            <h1 class="title title--h1 title__separate"><?= s('project_page_title') ?></h1>
                        </div>

                        <a class="btn-back" href="../portfolio"><i class="feathericon-arrow-right"></i><?= s('portfolio_back_text') ?></a>

                        <header class="header-project">
                            <h1 class="title title--h1"><?= $project['heading_html'] ?></h1>
<?php if ($project['header_image'] !== ''): ?>
                            <div class="header-project__image-wrap">
                                <img class="cover lazyload" src="<?= asset($project['header_image'], $prefix) ?>" alt="" />
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
                                        <img class="lazyload" src="<?= asset($img['path'], $prefix) ?>" alt="" />
                                    </a>
                                </figure>
<?php endforeach; ?>
                            </div>
                            <div class="swiper-pagination"></div>
                        </div>
<?php endif; ?>
<?php require __DIR__ . '/app/views/layout_bottom.php';
