<?php
require __DIR__ . '/app/bootstrap.php';

$prefix = '';
$active = 'about';
$title = setting('seo_index_title');
$description = setting('seo_index_description');

$services = rows('SELECT * FROM services ORDER BY sort, id');
$skills = rows('SELECT text FROM skills ORDER BY sort, id');
$skillColumns = array_chunk($skills, max(1, (int) ceil(count($skills) / 2)));
$experiences = rows('SELECT * FROM experiences ORDER BY sort, id');

ob_start();
foreach ($experiences as $exp): ?>
    <div id="review-<?= (int) $exp['id'] ?>" class="popup mfp-hide mfp-with-anim">
        <div class="row">
            <div class="col-12 col-sm-2 full-rewiew-con-avatar">
                <figure class="box box-avatar">
                    <img src="<?= asset($exp['avatar']) ?>" alt="<?= e($exp['name']) ?>">
                </figure>
                <div class="review-icon-quote"></div>
            </div>
            <div class="col-12 col-sm-10 full-rewiew-con-text">
                <h2 class="title title--h2 mb-1"><?= e($exp['name']) ?></h2>
                <span class="review-date"><?= e($exp['role']) ?></span>
                <?= $exp['body_html'] ?>
            </div>
        </div>
    </div>
<?php endforeach;
$afterMain = ob_get_clean();

require __DIR__ . '/app/views/layout_top.php';
?>

                        <div class="pb-0 pb-sm-2">
                            <h1 class="title title--h1 title__separate"><?= s('about_title') ?></h1>
                            <?= setting('about_html') ?>
                        </div>

<?php if ($services): ?>
                        <h2 class="title title--h2 mt-3"><?= s('services_title') ?></h2>
                        <div class="row">
<?php foreach ($services as $service): ?>
                            <div class="col-12 col-lg-6">
                                <div class="case-item box box-inner">
                                    <img class="case-item__icon" src="<?= asset($service['icon']) ?>" alt="<?= e($service['icon_alt']) ?>" />
                                    <div>
                                        <h3 class="title title--h3"><?= e($service['title']) ?></h3>
                                        <p class="case-item__caption"><?= e($service['description']) ?></p>
                                    </div>
                                </div>
                            </div>
<?php endforeach; ?>
                        </div>
<?php endif; ?>

<?php if ($skills): ?>
                        <h2 class="title title--h2 mt-3"><?= s('skills_title') ?></h2>
                        <div class="box box-inner">
                            <div class="row">
<?php foreach ($skillColumns as $column): ?>
                                <div class="col-12 col-md-6">
                                    <ul class="list-unstyled">
<?php foreach ($column as $skill): ?>
                                        <li>✔ <?= e($skill['text']) ?></li>
<?php endforeach; ?>
                                    </ul>
                                </div>
<?php endforeach; ?>
                            </div>
                        </div>
<?php endif; ?>

<?php if ($experiences): ?>
                        <div class="testimonials">
                            <h2 class="title title--h2 mt-3"><?= s('experience_title') ?></h2>

                            <div class="swiper-container js-carousel-review">
                                <div class="swiper-wrapper">
<?php foreach ($experiences as $exp): ?>
                                    <div class="swiper-slide review-item box box-inner js-open-review"
                                        data-mfp-src="#review-<?= (int) $exp['id'] ?>" data-effect="mfp-zoom-out">
                                        <figure class="box box-avatar">
                                            <img src="<?= asset($exp['avatar']) ?>" alt="<?= e($exp['name']) ?>">
                                        </figure>
                                        <h4 class="title title--h3"><?= e($exp['name']) ?></h4>
                                        <p class="review-item__caption"><?= e($exp['caption']) ?></p>
                                    </div>
<?php endforeach; ?>
                                </div>

                                <div class="swiper-pagination"></div>
                            </div>
                        </div>
<?php endif; ?>
<?php require __DIR__ . '/app/views/layout_bottom.php';
