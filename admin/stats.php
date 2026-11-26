<?php
require dirname(__DIR__) . '/app/admin/core.php';
require_admin();

$pageTitle = 'آمار بازدید';
$menu = 'stats';

$ranges = [1 => 'امروز', 7 => '۷ روز اخیر', 30 => '۳۰ روز اخیر', 90 => '۹۰ روز اخیر'];
$days = (int) ($_GET['d'] ?? 30);
if (!isset($ranges[$days])) {
    $days = 30;
}
$from = date('Y-m-d 00:00:00', strtotime('-' . ($days - 1) . ' days'));
$num = static fn(string $sql, array $p = []): int => (int) (row($sql, $p)['c'] ?? 0);

$ready = true;
try {
    q('SELECT 1 FROM visits LIMIT 1');
} catch (Throwable $e) {
    $ready = false;
}

if ($ready) {
    $views = $num('SELECT COUNT(*) c FROM visits WHERE created_at >= ?', [$from]);
    $visitors = $num('SELECT COUNT(DISTINCT visitor) c FROM visits WHERE created_at >= ?', [$from]);
    $todayViews = $num('SELECT COUNT(*) c FROM visits WHERE created_at >= ?', [date('Y-m-d 00:00:00')]);
    $online = $num('SELECT COUNT(DISTINCT visitor) c FROM visits WHERE created_at >= ?', [date('Y-m-d H:i:s', time() - 300)]);
    $postViews = $num('SELECT COALESCE(SUM(views), 0) c FROM posts');
    $projectViews = $num('SELECT COALESCE(SUM(views), 0) c FROM projects');

    // Daily chart (hourly for "today").
    $chart = [];
    if ($days === 1) {
        foreach (rows('SELECT HOUR(created_at) h, COUNT(*) v, COUNT(DISTINCT visitor) u FROM visits WHERE created_at >= ? GROUP BY HOUR(created_at)', [$from]) as $r) {
            $chart[(int) $r['h']] = $r;
        }
        $bars = [];
        for ($h = 0; $h < 24; $h++) {
            $bars[] = [fa_digits(sprintf('%02d:00', $h)), (int) ($chart[$h]['v'] ?? 0), (int) ($chart[$h]['u'] ?? 0)];
        }
    } else {
        foreach (rows('SELECT DATE(created_at) d, COUNT(*) v, COUNT(DISTINCT visitor) u FROM visits WHERE created_at >= ? GROUP BY DATE(created_at)', [$from]) as $r) {
            $chart[$r['d']] = $r;
        }
        $bars = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $t = strtotime('-' . $i . ' days');
            $d = date('Y-m-d', $t);
            $bars[] = [jalali_date($t), (int) ($chart[$d]['v'] ?? 0), (int) ($chart[$d]['u'] ?? 0)];
        }
    }
    $max = max(1, max(array_column($bars, 1)));

    $topPages = rows('SELECT path, COUNT(*) v, COUNT(DISTINCT visitor) u FROM visits WHERE created_at >= ? GROUP BY path ORDER BY v DESC LIMIT 10', [$from]);
    $rangePosts = [];
    foreach (rows("SELECT ref_id, COUNT(*) v FROM visits WHERE page_type = 'post' AND created_at >= ? GROUP BY ref_id", [$from]) as $r) {
        $rangePosts[(int) $r['ref_id']] = (int) $r['v'];
    }
    $rangeProjects = [];
    foreach (rows("SELECT ref_id, COUNT(*) v FROM visits WHERE page_type = 'project' AND created_at >= ? GROUP BY ref_id", [$from]) as $r) {
        $rangeProjects[(int) $r['ref_id']] = (int) $r['v'];
    }
    $posts = rows('SELECT id, slug, title, views, is_published FROM posts ORDER BY views DESC, id DESC');
    $projects = rows('SELECT id, slug, title, views, is_published FROM projects ORDER BY views DESC, id');

    $group = static function (string $col) use ($from): array {
        return rows("SELECT {$col} k, COUNT(DISTINCT visitor) v FROM visits WHERE created_at >= ? GROUP BY {$col} ORDER BY v DESC LIMIT 8", [$from]);
    };
    $referrers = rows("SELECT referrer k, COUNT(*) v FROM visits WHERE created_at >= ? AND referrer <> '' GROUP BY referrer ORDER BY v DESC LIMIT 10", [$from]);
    $directViews = $num("SELECT COUNT(*) c FROM visits WHERE created_at >= ? AND referrer = ''", [$from]);
    $devices = $group('device');
    $browsers = $group('browser');
    $systems = $group('os');

    // Recent visitors, paged.
    $page = max(1, (int) ($_GET['page'] ?? 1));
    $perPage = 25;
    $totalRecent = $num('SELECT COUNT(*) c FROM visits WHERE created_at >= ?', [$from]);
    $pages = max(1, (int) ceil($totalRecent / $perPage));
    $page = min($page, $pages);
    $recent = rows('SELECT * FROM visits WHERE created_at >= ? ORDER BY id DESC LIMIT ' . $perPage . ' OFFSET ' . (($page - 1) * $perPage), [$from]);
}

$deviceNames = ['desktop' => 'دسکتاپ', 'mobile' => 'موبایل', 'tablet' => 'تبلت'];
$deviceIcons = ['desktop' => 'feathericon-monitor', 'mobile' => 'feathericon-smartphone', 'tablet' => 'feathericon-smartphone'];
$pathLabel = static function (string $path): string {
    $map = ['/' => 'درباره من (صفحه اصلی)', '/index' => 'درباره من (صفحه اصلی)', '/resume' => 'رزومه', '/portfolio' => 'نمونه کارها', '/blog' => 'وبلاگ', '/contact' => 'تماس'];
    return $map[rtrim($path, '/') === '' ? '/' : rtrim($path, '/')] ?? $path;
};
$siteLink = static fn(string $path): string => '/' . ltrim(implode('/', array_map('rawurlencode', explode('/', $path))), '/');

require dirname(__DIR__) . '/app/admin/layout_top.php';
?>
<?php if (!$ready): ?>
                        <div class="box box-inner admin-empty">جدول آمار هنوز ساخته نشده است. صفحهٔ اصلی سایت را یک بار باز کنید تا به‌روزرسانی خودکار انجام شود و دوباره همین صفحه را ببینید.</div>
<?php else: ?>
                        <div class="admin-tabs">
<?php foreach ($ranges as $d => $label): ?>
                            <a class="<?= $d === $days ? 'is-active' : '' ?>" href="stats.php?d=<?= $d ?>"><?= e($label) ?></a>
<?php endforeach; ?>
                        </div>

                        <div class="row">
<?php foreach ([
    ['feathericon-eye', 'بازدید صفحات', $views, $ranges[$days]],
    ['feathericon-users', 'بازدیدکنندهٔ یکتا', $visitors, $ranges[$days]],
    ['feathericon-activity', 'آنلاین الان', $online, 'در ۵ دقیقهٔ اخیر · امروز ' . views_text($todayViews) . ' بازدید'],
    ['feathericon-edit', 'کل بازدید وبلاگ', $postViews, 'نمونه کارها: ' . views_text($projectViews) . ' بازدید'],
] as [$icon, $label, $n, $sub]): ?>
                            <div class="col-12 col-lg-6">
                                <div class="case-item box box-inner admin-stat">
                                    <span class="box icon-box"><i class="font-icon <?= e($icon) ?>"></i></span>
                                    <div>
                                        <div class="admin-stat__num"><?= views_text($n) ?></div>
                                        <h3 class="title title--h3"><?= e($label) ?></h3>
                                        <p class="case-item__caption"><?= e($sub) ?></p>
                                    </div>
                                </div>
                            </div>
<?php endforeach; ?>
                        </div>

                        <h2 class="title title--h2 mt-3"><?= $days === 1 ? 'بازدید ساعت به ساعت' : 'بازدید روزانه' ?></h2>
                        <div class="box box-inner">
                            <div class="admin-chart" dir="ltr">
<?php foreach ($bars as [$label, $v, $u]): ?>
                                <div class="admin-chart__col" data-tip="<?= e($label . ' · ' . views_text($v) . ' بازدید · ' . views_text($u) . ' نفر') ?>">
                                    <div class="admin-chart__bar" style="height: <?= round($v / $max * 100, 1) ?>%"></div>
                                    <div class="admin-chart__bar--uniq" style="height: <?= round($u / $max * 100, 1) ?>%"></div>
                                </div>
<?php endforeach; ?>
                            </div>
                            <div class="admin-chart__axis"><span><?= e(end($bars)[0]) ?></span><span><?= e($bars[0][0]) ?></span></div>
                            <div class="admin-legend"><span class="is-views">بازدید</span><span class="is-uniq">بازدیدکنندهٔ یکتا</span></div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-lg-6">
                                <h2 class="title title--h2 mt-4">پست‌های وبلاگ</h2>
<?php if (!$posts): ?>
                                <div class="box box-inner admin-empty">هنوز پستی ندارید.</div>
<?php else: ?>
                                <div class="admin-table-wrap box box-inner">
                                    <table class="admin-table">
                                        <thead><tr><th>عنوان</th><th><?= e($ranges[$days]) ?></th><th>کل</th></tr></thead>
                                        <tbody>
<?php foreach ($posts as $r): ?>
                                            <tr>
                                                <td data-label="عنوان"><a href="../Blogs/<?= e(rawurlencode($r['slug'])) ?>" target="_blank"><?= e(mb_strimwidth($r['title'], 0, 55, '…')) ?></a><?= $r['is_published'] ? '' : ' <span class="admin-pill">پیش‌نویس</span>' ?></td>
                                                <td class="admin-num" data-label="<?= e($ranges[$days]) ?>"><?= views_text($rangePosts[(int) $r['id']] ?? 0) ?></td>
                                                <td class="admin-num" data-label="کل"><?= views_text($r['views']) ?></td>
                                            </tr>
<?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
<?php endif; ?>
                            </div>
                            <div class="col-12 col-lg-6">
                                <h2 class="title title--h2 mt-4">نمونه کارها</h2>
<?php if (!$projects): ?>
                                <div class="box box-inner admin-empty">هنوز نمونه کاری ندارید.</div>
<?php else: ?>
                                <div class="admin-table-wrap box box-inner">
                                    <table class="admin-table">
                                        <thead><tr><th>عنوان</th><th><?= e($ranges[$days]) ?></th><th>کل</th></tr></thead>
                                        <tbody>
<?php foreach ($projects as $r): ?>
                                            <tr>
                                                <td data-label="عنوان"><a href="../Projects/<?= e(rawurlencode($r['slug'])) ?>" target="_blank"><?= e(mb_strimwidth($r['title'], 0, 55, '…')) ?></a><?= $r['is_published'] ? '' : ' <span class="admin-pill">پیش‌نویس</span>' ?></td>
                                                <td class="admin-num" data-label="<?= e($ranges[$days]) ?>"><?= views_text($rangeProjects[(int) $r['id']] ?? 0) ?></td>
                                                <td class="admin-num" data-label="کل"><?= views_text($r['views']) ?></td>
                                            </tr>
<?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
<?php endif; ?>
                            </div>
                        </div>

                        <h2 class="title title--h2 mt-4">پربازدیدترین صفحات</h2>
<?php if (!$topPages): ?>
                        <div class="box box-inner admin-empty">در این بازه هنوز بازدیدی ثبت نشده است.</div>
<?php else: ?>
                        <div class="admin-table-wrap box box-inner">
                            <table class="admin-table">
                                <thead><tr><th>صفحه</th><th>بازدید</th><th>نفر</th></tr></thead>
                                <tbody>
<?php foreach ($topPages as $r): ?>
                                    <tr>
                                        <td data-label="صفحه">
                                            <a href="<?= e($siteLink($r['path'])) ?>" target="_blank"><?= e($pathLabel($r['path'])) ?></a>
                                            <div class="admin-meter"><span style="width: <?= round($r['v'] / max(1, $topPages[0]['v']) * 100, 1) ?>%"></span></div>
                                        </td>
                                        <td class="admin-num" data-label="بازدید"><?= views_text($r['v']) ?></td>
                                        <td class="admin-num" data-label="نفر"><?= views_text($r['u']) ?></td>
                                    </tr>
<?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
<?php endif; ?>

                        <div class="row">
<?php foreach ([
    ['ورودی از سایت‌های دیگر', $referrers, static fn($k) => $k, 'بازدید'],
    ['دستگاه', $devices, static fn($k) => $deviceNames[$k] ?? $k, 'نفر'],
    ['مرورگر', $browsers, static fn($k) => $k, 'نفر'],
    ['سیستم‌عامل', $systems, static fn($k) => $k, 'نفر'],
] as [$heading, $list, $label, $unit]): $top = max(1, (int) ($list[0]['v'] ?? 1)); ?>
                            <div class="col-12 col-lg-6">
                                <h2 class="title title--h2 mt-4"><?= e($heading) ?></h2>
                                <div class="admin-table-wrap box box-inner">
<?php if (!$list): ?>
                                    <p class="admin-empty mb-0 py-2"><?= $heading === 'ورودی از سایت‌های دیگر' ? 'همهٔ ورودی‌ها مستقیم بوده‌اند.' : 'داده‌ای نیست.' ?></p>
<?php else: ?>
                                    <table class="admin-table">
                                        <tbody>
<?php foreach ($list as $r): ?>
                                            <tr>
                                                <td><span dir="auto"><?= e($label((string) $r['k'])) ?></span><div class="admin-meter"><span style="width: <?= round($r['v'] / $top * 100, 1) ?>%"></span></div></td>
                                                <td class="admin-num"><?= views_text($r['v']) ?> <span class="admin-muted"><?= e($unit) ?></span></td>
                                            </tr>
<?php endforeach; ?>
                                        </tbody>
                                    </table>
<?php endif; ?>
<?php if ($heading === 'ورودی از سایت‌های دیگر'): ?>
                                    <p class="admin-muted mb-0 pb-2">ورود مستقیم یا بدون ارجاع: <?= views_text($directViews) ?> بازدید</p>
<?php endif; ?>
                                </div>
                            </div>
<?php endforeach; ?>
                        </div>

                        <h2 class="title title--h2 mt-4">آخرین بازدیدکنندگان</h2>
<?php if (!$recent): ?>
                        <div class="box box-inner admin-empty">در این بازه هنوز بازدیدی ثبت نشده است.</div>
<?php else: ?>
                        <div class="admin-table-wrap box box-inner">
                            <table class="admin-table">
                                <thead><tr><th>زمان</th><th>صفحه</th><th>آی‌پی</th><th>دستگاه</th><th>از کجا</th></tr></thead>
                                <tbody>
<?php foreach ($recent as $v): $t = strtotime($v['created_at']); ?>
                                    <tr>
                                        <td data-label="زمان" class="text-nowrap"><?= e(jalali_date($t)) ?> <span class="admin-muted"><?= e(fa_digits(date('H:i', $t))) ?></span></td>
                                        <td data-label="صفحه"><a href="<?= e($siteLink($v['path'])) ?>" target="_blank"><?= e(mb_strimwidth($pathLabel($v['path']), 0, 50, '…')) ?></a></td>
                                        <td data-label="آی‌پی" dir="ltr"><?= e($v['ip']) ?></td>
                                        <td data-label="دستگاه" title="<?= e($v['user_agent']) ?>"><i class="<?= e($deviceIcons[$v['device']] ?? 'feathericon-monitor') ?>"></i> <?= e($v['browser'] . ' · ' . $v['os']) ?></td>
                                        <td data-label="از کجا" dir="auto"><?= $v['referrer'] !== '' ? e($v['referrer']) : '<span class="admin-muted">مستقیم</span>' ?></td>
                                    </tr>
<?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
<?php if ($pages > 1): ?>
                        <div class="admin-pager">
<?php for ($i = max(1, $page - 4); $i <= min($pages, $page + 4); $i++): ?>
                            <a class="<?= $i === $page ? 'is-active' : '' ?>" href="stats.php?d=<?= $days ?>&amp;page=<?= $i ?>"><?= fa_digits($i) ?></a>
<?php endfor; ?>
                        </div>
<?php endif; ?>
<?php endif; ?>
                        <p class="admin-muted mt-3">ربات‌ها و مرورگر خود شما (وقتی وارد پنل شده‌اید) شمرده نمی‌شوند. هر نفر در هر صفحه حداکثر یک بار در هر ۳۰ دقیقه شمرده می‌شود و جزئیات بازدیدها <?= fa_digits(ANALYTICS_KEEP_DAYS) ?> روز نگه داشته می‌شود؛ شمارندهٔ کل پست‌ها و نمونه کارها پاک نمی‌شود.</p>
<?php endif; ?>
<?php require dirname(__DIR__) . '/app/admin/layout_bottom.php';
