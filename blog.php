<?php
require __DIR__ . '/app/bootstrap.php';

$prefix = '';
$active = 'blog';
$title = setting('seo_blog_title');
$description = setting('seo_blog_description');
$boxOuterClass = 'pb-0';

$posts = rows('SELECT * FROM posts WHERE is_published = 1 ORDER BY sort, id DESC');

require __DIR__ . '/app/views/layout_top.php';
?>

                        <div class="pb-0 pb-sm-2">
                            <h1 class="title title--h1 title__separate"><?= s('blog_title') ?></h1>
                        </div>

                        <div class="news-grid">
<?php if (!$posts): ?>
                            <p style="font-size: 45px;"><?= s('blog_empty_text') ?></p>
<?php endif; ?>
<?php foreach ($posts as $post): $url = 'Blogs/' . rawurlencode($post['slug']); ?>
                            <article class="news-item box">
                                <div class="news-item__image-wrap">
                                    <a class="news-item__link" href="<?= e($url) ?>"></a>
                                    <img class="cover lazyload" src="<?= asset($post['cover']) ?>" alt="" />
                                </div>
                                <div class="news-item__caption">
                                    <div class="news-item__date"><span><?= e($post['category']) ?></span><?= e($post['date_text']) ?></div>
                                    <h2 class="title title--h2"><a href="<?= e($url) ?>"><?= e($post['title']) ?></a></h2>
                                    <p><?= e($post['excerpt']) ?></p>
                                </div>
                            </article>
<?php endforeach; ?>
                        </div>
<?php require __DIR__ . '/app/views/layout_bottom.php';
