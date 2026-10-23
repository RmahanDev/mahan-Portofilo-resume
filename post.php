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
$description = $post['meta_desc'] !== '' ? $post['meta_desc'] : $post['excerpt'];
$scrollLine = true;
$htmlDir = true;

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$pageUrl = rawurlencode($scheme . '://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? ''));
$pageTitle = rawurlencode($post['title']);

require __DIR__ . '/app/views/layout_top.php';
?>

                        <div class="pb-0 pb-sm-2">
                            <h2 class="title title--h1 title__separate"><?= s('post_page_title') ?></h2>
                        </div>

                        <div class="pb-3">
                            <header class="header-post">
                                <h1 class="title title--h1"><?= e($post['title']) ?></h1>
<?php if (trim($post['intro_html']) !== ''): ?>
                                <div class="caption-post">
                                    <?= rich($post['intro_html'], $prefix) ?>
                                </div>
<?php endif; ?>
<?php if ($post['header_image'] !== ''): ?>
                                <div class="header-post__image-wrap">
                                    <img class="cover lazyload" src="<?= asset($post['header_image'], $prefix) ?>" alt="" />
                                </div>
<?php endif; ?>
                            </header>
                            <?= rich($post['body_html'], $prefix) ?>
                            <footer class="footer-post">
                                <a class="footer-post__share" href="https://www.facebook.com/sharer/sharer.php?u=<?= $pageUrl ?>"><i class="font-icon icon-facebook"></i><span>فیسبوک</span></a>
                                <a class="footer-post__share" href="https://twitter.com/intent/tweet?url=<?= $pageUrl ?>&amp;text=<?= $pageTitle ?>"><i class="font-icon icon-twitter"></i><span>توییتر</span></a>
                                <a class="footer-post__share" href="https://www.linkedin.com/sharing/share-offsite/?url=<?= $pageUrl ?>"><i class="font-icon icon-linkedin2"></i><span>لینکدین</span></a>
                            </footer>
                        </div>
<?php require __DIR__ . '/app/views/layout_bottom.php';
