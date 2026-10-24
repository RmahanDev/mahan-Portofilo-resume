<?php
require __DIR__ . '/app/bootstrap.php';

$prefix = '';
$active = 'resume';
$title = setting('seo_resume_title');
$description = setting('seo_resume_description');
$schema = schema_page('WebPage', 'resume', $title, $description, [[$title, 'resume']]);
$bodyClass = 'bg-triangles';

$sections = [
    'education'  => ['feathericon-book-open', setting('resume_education_title')],
    'experience' => ['feathericon-briefcase', setting('resume_experience_title')],
];
$items = [];
foreach (rows('SELECT * FROM resume_items ORDER BY sort, id') as $item) {
    $items[$item['section']][] = $item;
}
$groups = rows('SELECT * FROM skill_groups ORDER BY sort, id');
$bars = [];
foreach (rows('SELECT * FROM skill_bars ORDER BY sort, id') as $bar) {
    $bars[$bar['group_id']][] = $bar;
}

require __DIR__ . '/app/views/layout_top.php';
?>

                        <div class="pb-3">
                            <h1 class="title title--h1 title__separate"><?= s('resume_title') ?></h1>
                        </div>

<?php foreach ($sections as $key => [$icon, $heading]): if (empty($items[$key])) continue; ?>
                        <h2 class="title title--h2"><span class="box icon-box"><i
                                    class="font-icon <?= $icon ?>"></i></span><?= e($heading) ?></h2>
                        <div class="timeline">
<?php foreach ($items[$key] as $item): ?>
                            <article class="timeline__item">
                                <h5 class="title title--h5 timeline__title"><?= e($item['title']) ?></h5>
                                <span class="timeline__period"><?= e($item['period']) ?></span>
                                <p class="timeline__description"><?= e($item['description']) ?></p>
                            </article>
<?php endforeach; ?>
                        </div>

<?php endforeach; ?>
<?php if ($groups): ?>
                        <h2 class="title title--h2 mt-3"><?= s('resume_skills_title') ?></h2>
                        <div class="box box-inner mb-0">
<?php foreach ($groups as $i => $group): ?>
                            <p<?= $i > 0 ? ' class="mt-4"' : '' ?>><strong><?= e($group['title']) ?>:</strong></p>
<?php foreach ($bars[$group['id']] ?? [] as $bar): $pct = max(0, min(100, (int) $bar['percent'])); ?>
                            <div class="progress">
                                <div class="progress-text"><span><?= e($bar['label']) ?></span> <?= $pct ?>%</div>
                                <div class="progress-bar"><span role="progressbar" style="width: <?= $pct ?>%"></span></div>
                            </div>
<?php endforeach; ?>
<?php endforeach; ?>
                        </div>
<?php endif; ?>
<?php require __DIR__ . '/app/views/layout_bottom.php';
