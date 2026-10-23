<?php
/**
 * One-time web installer: writes config/config.php, creates the tables,
 * imports the site's current content and creates the admin account.
 * Once config/config.php exists this page refuses to run again.
 */

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
date_default_timezone_set('Asia/Tehran');
require ROOT_PATH . '/app/helpers.php';
require __DIR__ . '/seed.php';

$configFile = ROOT_PATH . '/config/config.php';
$installed = is_file($configFile);

$checks = [
    'PHP نسخه 7.4 یا بالاتر (فعلی: ' . PHP_VERSION . ')' => version_compare(PHP_VERSION, '7.4.0', '>='),
    'افزونه pdo_mysql'                                    => extension_loaded('pdo_mysql'),
    'افزونه mbstring'                                     => extension_loaded('mbstring'),
    'پوشه config قابل نوشتن است'                          => is_writable(ROOT_PATH . '/config'),
    'پوشه uploads قابل نوشتن است'                         => is_writable(ROOT_PATH . '/uploads'),
];
$ready = !in_array(false, $checks, true);

$error = '';
$done = false;
$old = ['db_host' => 'localhost', 'db_name' => '', 'db_user' => '', 'admin_user' => 'admin'];

if (!$installed && $ready && $_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $k => $_) {
        $old[$k] = trim((string) ($_POST[$k] ?? ''));
    }
    $dbPass = (string) ($_POST['db_pass'] ?? '');
    $adminPass = (string) ($_POST['admin_pass'] ?? '');

    try {
        if ($old['db_name'] === '' || $old['db_user'] === '') {
            throw new RuntimeException('نام دیتابیس و نام کاربری دیتابیس را وارد کنید.');
        }
        if (!preg_match('~^[A-Za-z0-9_.\-]{3,60}$~', $old['admin_user'])) {
            throw new RuntimeException('نام کاربری ادمین باید ۳ تا ۶۰ کاراکتر انگلیسی باشد.');
        }
        if (mb_strlen($adminPass) < 8) {
            throw new RuntimeException('رمز عبور ادمین باید حداقل ۸ کاراکتر باشد.');
        }
        if ($adminPass !== (string) ($_POST['admin_pass_confirm'] ?? '')) {
            throw new RuntimeException('تکرار رمز عبور ادمین یکسان نیست.');
        }

        try {
            $pdo = db_connect($old['db_host'], $old['db_name'], $old['db_user'], $dbPass);
        } catch (PDOException $ex) {
            throw new RuntimeException('اتصال به دیتابیس برقرار نشد. نام دیتابیس، کاربر، رمز و دسترسی کاربر به دیتابیس را بررسی کنید. (' . $ex->getMessage() . ')');
        }

        run_schema($pdo, __DIR__ . '/schema.sql');

        // Re-running on a database that already has content (e.g. config.php was lost) keeps that content.
        $hasContent = (int) $pdo->query('SELECT COUNT(*) FROM settings')->fetchColumn() > 0;
        if (!$hasContent) {
            $seed = json_decode((string) file_get_contents(__DIR__ . '/seed.json'), true);
            seed_database($pdo, $seed);
        }

        $hash = password_hash($adminPass, PASSWORD_DEFAULT);
        $pdo->prepare('INSERT INTO admins (username, password_hash) VALUES (?, ?)
                       ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)')
            ->execute([$old['admin_user'], $hash]);

        $config = [
            'db' => [
                'host'    => $old['db_host'],
                'name'    => $old['db_name'],
                'user'    => $old['db_user'],
                'pass'    => $dbPass,
                'charset' => 'utf8mb4',
            ],
            'debug' => false,
        ];
        $php = "<?php\n// Created by the installer on " . date('Y-m-d H:i') . ". Not tracked by git.\nreturn " . var_export($config, true) . ";\n";
        if (file_put_contents($configFile, $php, LOCK_EX) === false) {
            throw new RuntimeException('نوشتن فایل config/config.php ممکن نشد. دسترسی پوشه config را 755 کنید.');
        }
        @chmod($configFile, 0640);
        $done = true;
        $installed = true;
    } catch (Throwable $ex) {
        $error = $ex->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="utf-8" />
    <title>نصب سایت</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" type="text/css" href="../assets/styles/vendors/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="../assets/styles/style.css" />
    <link rel="stylesheet" type="text/css" href="../assets/styles/rtl.css" />
    <link rel="stylesheet" type="text/css" href="../admin/assets/admin.css" />
</head>

<body class="admin admin-auth">
    <main class="admin-auth__wrap">
        <div class="box-outer admin-auth__box admin-auth__box--wide">
            <h1 class="title title--h1 title__separate">نصب سایت</h1>

<?php if ($done): ?>
            <div class="admin-alert admin-alert--success">نصب با موفقیت انجام شد. محتوای فعلی سایت به دیتابیس منتقل شد.</div>
            <p>از این به بعد همه چیز را از پنل مدیریت ویرایش کنید. این صفحه دیگر قابل اجرا نیست.</p>
            <div class="admin-actions">
                <a class="btn" href="../admin/login.php"><i class="feathericon-log-in"></i> ورود به پنل مدیریت</a>
                <a class="btn" href="../index">مشاهده سایت</a>
            </div>
<?php elseif ($installed): ?>
            <div class="admin-alert admin-alert--success">سایت قبلا نصب شده است.</div>
            <p>برای نصب دوباره، فایل <code>config/config.php</code> را از File Manager هاست حذف کنید (محتوای دیتابیس حفظ می‌شود).</p>
            <a class="btn" href="../admin/login.php">ورود به پنل مدیریت</a>
<?php else: ?>
            <ul class="admin-checklist">
<?php foreach ($checks as $label => $ok): ?>
                <li class="<?= $ok ? 'ok' : 'bad' ?>"><?= $ok ? '✔' : '✖' ?> <?= e($label) ?></li>
<?php endforeach; ?>
            </ul>
<?php if (!$ready): ?>
            <div class="admin-alert admin-alert--error">موارد قرمز را در هاست درست کنید و صفحه را دوباره باز کنید.</div>
<?php else: ?>
<?php if ($error): ?>
            <div class="admin-alert admin-alert--error"><?= e($error) ?></div>
<?php endif; ?>
            <form method="post" class="admin-form" autocomplete="off">
                <h2 class="title title--h2">اطلاعات دیتابیس (از cPanel)</h2>
                <div class="row">
                    <div class="col-12 col-md-6"><div class="form-group">
                        <label class="admin-label">هاست دیتابیس</label>
                        <input class="input form-control" dir="ltr" name="db_host" value="<?= e($old['db_host']) ?>" required>
                    </div></div>
                    <div class="col-12 col-md-6"><div class="form-group">
                        <label class="admin-label">نام دیتابیس</label>
                        <input class="input form-control" dir="ltr" name="db_name" value="<?= e($old['db_name']) ?>" placeholder="cpaneluser_portfolio" required>
                    </div></div>
                    <div class="col-12 col-md-6"><div class="form-group">
                        <label class="admin-label">نام کاربری دیتابیس</label>
                        <input class="input form-control" dir="ltr" name="db_user" value="<?= e($old['db_user']) ?>" required>
                    </div></div>
                    <div class="col-12 col-md-6"><div class="form-group">
                        <label class="admin-label">رمز دیتابیس</label>
                        <input class="input form-control" dir="ltr" type="password" name="db_pass">
                    </div></div>
                </div>

                <h2 class="title title--h2">حساب مدیر سایت</h2>
                <div class="row">
                    <div class="col-12"><div class="form-group">
                        <label class="admin-label">نام کاربری ادمین</label>
                        <input class="input form-control" dir="ltr" name="admin_user" value="<?= e($old['admin_user']) ?>" required>
                    </div></div>
                    <div class="col-12 col-md-6"><div class="form-group">
                        <label class="admin-label">رمز عبور (حداقل ۸ کاراکتر)</label>
                        <input class="input form-control" dir="ltr" type="password" name="admin_pass" minlength="8" required autocomplete="new-password">
                    </div></div>
                    <div class="col-12 col-md-6"><div class="form-group">
                        <label class="admin-label">تکرار رمز عبور</label>
                        <input class="input form-control" dir="ltr" type="password" name="admin_pass_confirm" minlength="8" required autocomplete="new-password">
                    </div></div>
                </div>
                <button type="submit" class="btn w-100">نصب</button>
            </form>
<?php endif; ?>
<?php endif; ?>
        </div>
    </main>
</body>

</html>
