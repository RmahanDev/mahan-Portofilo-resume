<?php
require dirname(__DIR__) . '/app/admin/core.php';

if (current_admin()) {
    redirect('index.php');
}

$error = '';
$ip = client_ip();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_post();
    if (login_locked($ip)) {
        $error = 'به دلیل تلاش‌های ناموفق زیاد، ورود برای ' . LOGIN_LOCK_MINUTES . ' دقیقه قفل شده است.';
    } else {
        $admin = row('SELECT * FROM admins WHERE username = ?', [trim((string) ($_POST['username'] ?? ''))]);
        if ($admin && password_verify((string) ($_POST['password'] ?? ''), $admin['password_hash'])) {
            login_succeeded($ip, $admin);
            redirect('index.php');
        }
        login_failed($ip);
        $error = 'نام کاربری یا رمز عبور اشتباه است.';
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="utf-8" />
    <title>ورود به پنل مدیریت</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="robots" content="noindex, nofollow">
    <link rel="shortcut icon" href="../assets/images/favicons/favicon.png" type="image/png">
    <link rel="stylesheet" type="text/css" href="../assets/styles/vendors/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="../assets/styles/style.css" />
    <link rel="stylesheet" type="text/css" href="../assets/styles/rtl.css" />
    <link rel="stylesheet" type="text/css" href="assets/admin.css?v=1" />
</head>

<body class="admin admin-auth">
    <main class="admin-auth__wrap">
        <div class="box-outer admin-auth__box">
            <div class="sidebar__base-info admin-auth__head">
                <figure class="avatar-box">
                    <img src="<?= asset(setting('profile_avatar'), '../') ?>" alt="<?= s('profile_name') ?>">
                </figure>
                <div class="text-center">
                    <h3 class="title title--h3 sidebar__name"><?= s('profile_name') ?></h3>
                    <div class="badge">ورود به پنل مدیریت</div>
                </div>
            </div>

            <div class="separation"></div>

<?php if ($error): ?>
            <div class="admin-alert admin-alert--error"><?= e($error) ?></div>
<?php endif; ?>

            <form method="post" autocomplete="on">
                <?= csrf_field() ?>
                <div class="form-group">
                    <input type="text" class="input form-control" name="username" placeholder="نام کاربری" required autofocus dir="ltr">
                </div>
                <div class="form-group">
                    <input type="password" class="input form-control" name="password" placeholder="رمز عبور" required dir="ltr">
                </div>
                <button type="submit" class="btn w-100"><i class="feathericon-log-in"></i> ورود</button>
            </form>
            <a class="admin-auth__back" href="../index">بازگشت به سایت</a>
        </div>
    </main>
</body>

</html>
