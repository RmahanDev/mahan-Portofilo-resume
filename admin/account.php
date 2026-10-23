<?php
require dirname(__DIR__) . '/app/admin/core.php';
$admin = require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_post();
    $current = row('SELECT * FROM admins WHERE id = ?', [$admin['id']]);
    $username = trim((string) ($_POST['username'] ?? ''));
    $new = (string) ($_POST['new_password'] ?? '');

    if (!password_verify((string) ($_POST['current_password'] ?? ''), $current['password_hash'])) {
        flash('error', 'رمز عبور فعلی اشتباه است.');
    } elseif (!preg_match('~^[A-Za-z0-9_.\-]{3,60}$~', $username)) {
        flash('error', 'نام کاربری باید ۳ تا ۶۰ کاراکتر انگلیسی باشد.');
    } elseif ($new !== '' && mb_strlen($new) < 8) {
        flash('error', 'رمز عبور جدید باید حداقل ۸ کاراکتر باشد.');
    } elseif ($new !== '' && $new !== (string) ($_POST['new_password_confirm'] ?? '')) {
        flash('error', 'تکرار رمز عبور جدید یکسان نیست.');
    } elseif (row('SELECT id FROM admins WHERE username = ? AND id <> ?', [$username, $admin['id']])) {
        flash('error', 'این نام کاربری قبلا استفاده شده است.');
    } else {
        q('UPDATE admins SET username = ? WHERE id = ?', [$username, $admin['id']]);
        if ($new !== '') {
            q('UPDATE admins SET password_hash = ? WHERE id = ?', [password_hash($new, PASSWORD_DEFAULT), $admin['id']]);
            session_regenerate_id(true);
        }
        flash('success', 'حساب کاربری به‌روزرسانی شد.');
    }
    redirect('account.php');
}

$pageTitle = 'حساب کاربری';
$menu = 'account';
require dirname(__DIR__) . '/app/admin/layout_top.php';
?>
                        <form method="post" class="admin-form box box-inner" autocomplete="off">
                            <?= csrf_field() ?>
                            <div class="row">
                                <div class="col-12 col-md-6"><div class="form-group">
                                    <label class="admin-label" for="username">نام کاربری</label>
                                    <input class="input form-control" dir="ltr" id="username" name="username" value="<?= e($admin['username']) ?>" required>
                                </div></div>
                                <div class="col-12 col-md-6"><div class="form-group">
                                    <label class="admin-label" for="current_password">رمز عبور فعلی <span class="admin-required">*</span></label>
                                    <input class="input form-control" dir="ltr" type="password" id="current_password" name="current_password" required>
                                </div></div>
                                <div class="col-12 col-md-6"><div class="form-group">
                                    <label class="admin-label" for="new_password">رمز عبور جدید</label>
                                    <input class="input form-control" dir="ltr" type="password" id="new_password" name="new_password" minlength="8" autocomplete="new-password">
                                    <div class="admin-help">اگر نمی‌خواهید تغییر کند خالی بگذارید.</div>
                                </div></div>
                                <div class="col-12 col-md-6"><div class="form-group">
                                    <label class="admin-label" for="new_password_confirm">تکرار رمز عبور جدید</label>
                                    <input class="input form-control" dir="ltr" type="password" id="new_password_confirm" name="new_password_confirm" autocomplete="new-password">
                                </div></div>
                            </div>
                            <div class="admin-actions">
                                <button type="submit" class="btn"><i class="feathericon-save"></i> ذخیره</button>
                            </div>
                        </form>
<?php require dirname(__DIR__) . '/app/admin/layout_bottom.php';
