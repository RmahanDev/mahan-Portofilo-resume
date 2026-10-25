<?php
// Single blog post, served at /Blogs/{slug} (see .htaccess).
$GLOBALS['ROOT_DEPTH'] = 1;
require __DIR__ . '/app/bootstrap.php';

$prefix = '../';
$slug = (string) ($_GET['slug'] ?? '');
$post = row('SELECT * FROM posts WHERE slug = ? AND is_published = 1', [$slug]);
if (!$post) {
    not_found($prefix);
}

$active = 'blog';
$title = $post['title'];
$description = $post['meta_desc'] !== '' ? $post['meta_desc'] : ($post['excerpt'] !== '' ? $post['excerpt'] : meta_text($post['intro_html'] . ' ' . $post['body_html']));
$scrollLine = true;
$htmlDir = true;
$ogType = 'article';
$readMinutes = reading_minutes($post['intro_html'] . ' ' . $post['body_html']);
$related = latest_posts(2, (int) $post['id']);
$relatedProjects = related_projects(0, 3);

$titleSuffix = true;
$canonicalPath = 'Blogs/' . rawurlencode($post['slug']);
$postUrl = url_for($canonicalPath);
$pageUrl = rawurlencode($postUrl);
$pageTitle = rawurlencode($post['title']);

$image = $post['header_image'] !== '' ? $post['header_image'] : $post['cover'];
if ($image !== '') {
    $socialImage = absolute_url($image);
    $socialImageAlt = $post['title'];
}
$published = jalali_text_to_iso($post['date_text']) ?? substr((string) $post['created_at'], 0, 10);
$modified = iso_datetime($post['updated_at']);
if ($modified === null || substr($modified, 0, 10) < $published) {
    $modified = $published;
}
$articleMeta = ['published' => $published, 'modified' => $modified, 'section' => $post['category']];
$person = ['@id' => site_url() . '/#person'];
$schema = [
    array_filter([
        '@type'            => 'BlogPosting',
        '@id'              => $postUrl . '#article',
        'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $postUrl],
        'headline'         => mb_substr($post['title'], 0, 110),
        'description'      => $description,
        'image'            => $image !== '' ? [$socialImage] : null,
        'datePublished'    => $published,
        'dateModified'     => $modified,
        'inLanguage'       => 'fa-IR',
        'articleSection'   => $post['category'] !== '' ? $post['category'] : null,
        'wordCount'        => count(preg_split('~\s+~u', trim(strip_tags($post['intro_html'] . ' ' . $post['body_html'])), -1, PREG_SPLIT_NO_EMPTY)),
        'author'           => $person,
        'publisher'        => $person,
        'isPartOf'         => ['@id' => url_for('blog') . '#webpage'],
    ]),
    ['@id' => $postUrl . '#breadcrumb'] + schema_breadcrumb([
        [setting('profile_name'), site_url() . '/'],
        [setting('nav_blog'), url_for('blog')],
        [$post['title'], $postUrl],
    ]),
];

require __DIR__ . '/app/views/layout_top.php';
?>

                        <div class="pb-0 pb-sm-2">
                            <h2 class="title title--h1 title__separate"><?= s('post_page_title') ?></h2>
                        </div>

                        <div class="pb-3">
                            <header class="header-post">
                                <h1 class="title title--h1"><?= e($post['title']) ?></h1>
                                <ul class="post-meta">
<?php if ($post['category'] !== ''): ?>
                                    <li><i class="feathericon-tag"></i><?= e($post['category']) ?></li>
<?php endif; ?>
<?php if ($post['date_text'] !== ''): ?>
                                    <li><i class="feathericon-calendar"></i><?= e($post['date_text']) ?></li>
<?php endif; ?>
                                    <li><i class="feathericon-clock"></i>زمان مطالعه: <?= fa_digits($readMinutes) ?> دقیقه</li>
                                </ul>
<?php if (trim($post['intro_html']) !== ''): ?>
                                <div class="caption-post">
                                    <?= rich($post['intro_html'], $prefix) ?>
                                </div>
<?php endif; ?>
<?php if ($post['header_image'] !== ''): ?>
                                <div class="header-post__image-wrap">
                                    <img class="cover lazyload" src="<?= asset($post['header_image'], $prefix) ?>" alt="<?= e($post['title']) ?>" fetchpriority="high" />
                                </div>
<?php endif; ?>
                            </header>
                            <?= rich($post['body_html'], $prefix) ?>
                            <footer class="footer-post">
                                <a class="footer-post__share" href="https://www.facebook.com/sharer/sharer.php?u=<?= $pageUrl ?>"><i class="font-icon icon-facebook"></i><span>فیسبوک</span></a>
                                <a class="footer-post__share" href="https://twitter.com/intent/tweet?url=<?= $pageUrl ?>&amp;text=<?= $pageTitle ?>"><i class="font-icon icon-twitter"></i><span>توییتر</span></a>
                                <a class="footer-post__share" href="https://www.linkedin.com/sharing/share-offsite/?url=<?= $pageUrl ?>"><i class="font-icon icon-linkedin2"></i><span>لینکدین</span></a>
                            </footer>
<?php if ($related): ?>
                            <section class="related-posts">
                                <h2 class="title title--h2">مقاله‌های مرتبط</h2>
                                <div class="news-grid">
<?php foreach ($related as $r): $rUrl = rawurlencode($r['slug']); ?>
                                    <article class="news-item box">
                                        <div class="news-item__image-wrap">
                                            <a class="news-item__link" href="<?= e($rUrl) ?>"></a>
                                            <img class="cover lazyload" src="<?= asset($r['cover'], $prefix) ?>" alt="<?= e($r['title']) ?>" loading="lazy" decoding="async" />
                                        </div>
                                        <div class="news-item__caption">
                                            <div class="news-item__date"><span><?= e($r['category']) ?></span><?= e($r['date_text']) ?></div>
                                            <h2 class="title title--h2"><a href="<?= e($rUrl) ?>"><?= e($r['title']) ?></a></h2>
                                            <p><?= e($r['excerpt']) ?></p>
                                        </div>
                                    </article>
<?php endforeach; ?>
                                </div>
                            </section>
<?php endif; ?>
<?php
$linkHeading = 'نمونه‌کارهای من';
$linkCards = array_map(static fn($r) => ['url' => '../Projects/' . rawurlencode($r['slug']), 'title' => $r['title'], 'image' => $r['cover'], 'label' => $r['label']], $relatedProjects);
require __DIR__ . '/app/views/link_cards.php';
?>
                        </div>
<?php require __DIR__ . '/app/views/layout_bottom.php';
