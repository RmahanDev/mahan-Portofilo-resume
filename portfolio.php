<?php
require __DIR__ . '/app/bootstrap.php';

$prefix = '';
$active = 'portfolio';
$title = setting('seo_portfolio_title');
$description = setting('seo_portfolio_description');
$boxOuterClass = 'pb-0';

$categories = rows('SELECT * FROM categories ORDER BY sort, id');
$projects = rows('SELECT * FROM projects WHERE is_published = 1 ORDER BY sort, id');
$projectCats = [];
foreach (rows('SELECT pc.project_id, c.slug FROM project_categories pc JOIN categories c ON c.id = pc.category_id') as $r) {
    $projectCats[$r['project_id']][] = 'category-' . $r['slug'];
}

require __DIR__ . '/app/views/layout_top.php';
?>

                        <div class="pb-3">
                            <h1 class="title title--h1 title__separate"><?= s('portfolio_title') ?></h1>
                        </div>

<?php if (!$projects): ?>
                        <p class="portfolio-empty"><?= e(setting('portfolio_empty_text', 'نمونه‌کارها به‌زودی اضافه می‌شوند.')) ?></p>
<?php endif; ?>
                        <div class="select">
                            <span class="placeholder"><?= s('portfolio_filter_placeholder') ?></span>
                            <ul class="filter">
                                <li class="filter__item">Category</li>
                                <li class="filter__item active" data-filter="*"><a class="filter__link active"
                                        href="#filter"><?= s('portfolio_filter_all') ?></a></li>
<?php foreach ($categories as $cat): ?>
                                <li class="filter__item" data-filter=".category-<?= e($cat['slug']) ?>"><a class="filter__link"
                                        href="#filter"><?= e($cat['name']) ?></a></li>
<?php endforeach; ?>
                            </ul>
                            <input type="hidden" name="changemetoo" />
                        </div>

                        <div class="gallery-grid js-masonry js-filter-container">
                            <div class="gutter-sizer"></div>
<?php foreach ($projects as $project): ?>
                            <div class="gallery-grid__item <?= e(implode(' ', $projectCats[$project['id']] ?? [])) ?>">
                                <a href="Projects/<?= e(rawurlencode($project['slug'])) ?>">
                                    <div class="gallery-grid__image-wrap">
                                        <img class="gallery-grid__image cover lazyload"
                                            src="<?= asset($project['cover']) ?>" alt="" />
                                    </div>
                                    <div class="gallery-grid__caption">
                                        <h3 class="title gallery-grid__title"><?= e($project['title']) ?></h3>
                                        <span class="gallery-grid__category"><?= e($project['card_category']) ?></span>
                                    </div>
                                </a>
                            </div>
<?php endforeach; ?>
                        </div>
<?php require __DIR__ . '/app/views/layout_bottom.php';
