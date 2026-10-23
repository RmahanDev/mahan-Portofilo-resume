<?php
require dirname(__DIR__) . '/app/admin/core.php';
require_admin();

$pageTitle = 'داشبورد';
$menu = 'dashboard';

$count = static fn(string $sql): int => (int) row($sql)['c'];
$stats = [
    ['list.php?r=projects', 'feathericon-briefcase', 'نمونه کار منتشر شده', $count('SELECT COUNT(*) c FROM projects WHERE is_published = 1'), $count('SELECT COUNT(*) c FROM projects WHERE is_published = 0') . ' پیش‌نویس'],
    ['list.php?r=posts', 'feathericon-edit', 'پست منتشر شده', $count('SELECT COUNT(*) c FROM posts WHERE is_published = 1'), $count('SELECT COUNT(*) c FROM posts WHERE is_published = 0') . ' پیش‌نویس'],
    ['messages.php', 'feathericon-inbox', 'پیام خوانده نشده', unread_count(), $count('SELECT COUNT(*) c FROM messages') . ' پیام در کل'],
    ['list.php?r=experiences', 'feathericon-message-square', 'تجربه کاری', $count('SELECT COUNT(*) c FROM experiences'), 'در صفحه درباره من'],
];
$latest = rows('SELECT * FROM messages ORDER BY id DESC LIMIT 5');

require dirname(__DIR__) . '/app/admin/layout_top.php';
$migrationError = setting('migration_error');
?>
<?php if ($migrationError !== ''): ?>
                        <div class="admin-alert admin-alert--error">به‌روزرسانی خودکار محتوای سایت کامل نشد؛ بعد از چند دقیقه خودکار دوباره تلاش می‌شود. جزئیات: <span dir="ltr"><?= e(substr((string) strstr($migrationError, '|'), 1)) ?></span></div>
<?php endif; ?>
                        <div class="row">
<?php foreach ($stats as [$href, $icon, $label, $num, $sub]): ?>
                            <div class="col-12 col-lg-6">
                                <a class="case-item box box-inner admin-stat" href="<?= e($href) ?>">
                                    <span class="box icon-box"><i class="font-icon <?= e($icon) ?>"></i></span>
                                    <div>
                                        <div class="admin-stat__num"><?= $num ?></div>
                                        <h3 class="title title--h3"><?= e($label) ?></h3>
                                        <p class="case-item__caption"><?= e($sub) ?></p>
                                    </div>
                                </a>
                            </div>
<?php endforeach; ?>
                        </div>

                        <h2 class="title title--h2 mt-3">دسترسی سریع</h2>
                        <div class="admin-quick">
                            <a class="btn btn--small" href="edit.php?r=projects"><i class="feathericon-plus"></i> نمونه کار جدید</a>
                            <a class="btn btn--small" href="edit.php?r=posts"><i class="feathericon-plus"></i> پست جدید</a>
                            <a class="btn btn--small" href="settings.php?g=about"><i class="feathericon-home"></i> ویرایش درباره من</a>
                            <a class="btn btn--small" href="settings.php?g=resume"><i class="feathericon-file-text"></i> ویرایش رزومه</a>
                            <a class="btn btn--small" href="settings.php?g=profile"><i class="feathericon-user"></i> ویرایش پروفایل</a>
                        </div>

                        <h2 class="title title--h2 mt-4">آخرین پیام‌ها</h2>
<?php if (!$latest): ?>
                        <div class="box box-inner admin-empty">هنوز پیامی از فرم تماس نرسیده است.</div>
<?php else: ?>
                        <div class="admin-table-wrap box box-inner">
                            <table class="admin-table">
                                <thead><tr><th>فرستنده</th><th>موضوع</th><th>زمان</th></tr></thead>
                                <tbody>
<?php foreach ($latest as $m): ?>
                                    <tr class="<?= $m['is_read'] ? '' : 'is-unread' ?>">
                                        <td data-label="فرستنده"><a href="messages.php?id=<?= (int) $m['id'] ?>"><?= e($m['name']) ?></a></td>
                                        <td data-label="موضوع"><?= e($m['subject']) ?></td>
                                        <td data-label="زمان"><?= e(jalali_date(strtotime($m['created_at']))) ?></td>
                                    </tr>
<?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
<?php endif; ?>
<?php require dirname(__DIR__) . '/app/admin/layout_bottom.php';
