<?php
// Rendered by not_found(): keeps the site layout so visitors can navigate back.
$prefix = $GLOBALS['NOT_FOUND_PREFIX'] ?? '';
$active = '';
$title = 'صفحه پیدا نشد';
$description = '';

require __DIR__ . '/layout_top.php';
?>

                        <div class="pb-3">
                            <h1 class="title title--h1 title__separate">صفحه پیدا نشد</h1>
                        </div>
                        <p>صفحه‌ای که دنبالش بودید وجود ندارد یا جابه‌جا شده است.</p>
                        <a class="btn" href="<?= $prefix ?>index"><?= s('nav_about') ?></a>
<?php require __DIR__ . '/layout_bottom.php';
