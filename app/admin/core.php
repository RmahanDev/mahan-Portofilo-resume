<?php
/**
 * Admin panel core: session, authentication, CSRF, flash messages and uploads.
 * Every page in /admin requires this file first.
 */

declare(strict_types=1);

$GLOBALS['ROOT_DEPTH'] = 1;
require_once dirname(__DIR__) . '/bootstrap.php';
require_once __DIR__ . '/resources.php';
require_once __DIR__ . '/fields.php';

const LOGIN_MAX_ATTEMPTS = 5;
const LOGIN_LOCK_MINUTES = 15;
const UPLOAD_MAX_BYTES = 8 * 1024 * 1024;

session_name('pf_admin');
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');

function current_admin(): ?array
{
    if (empty($_SESSION['admin_id'])) {
        return null;
    }
    static $admin = null;
    if ($admin === null) {
        $admin = row('SELECT id, username FROM admins WHERE id = ?', [$_SESSION['admin_id']]);
    }
    return $admin;
}

function require_admin(): array
{
    $admin = current_admin();
    if (!$admin) {
        redirect('login.php');
    }
    return $admin;
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

/** Stops the request unless it is a POST carrying this session's CSRF token. */
function verify_post(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST'
        || !hash_equals(csrf_token(), (string) ($_POST['_csrf'] ?? ''))) {
        http_response_code(419);
        exit('درخواست نامعتبر است. صفحه را دوباره بارگذاری کنید.');
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = [$type, $message];
}

function take_flashes(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

/* ---------- login throttling ---------- */

function login_locked(string $ip): bool
{
    $r = row('SELECT attempts, last_at FROM login_attempts WHERE ip = ?', [$ip]);
    if (!$r) {
        return false;
    }
    $expired = strtotime($r['last_at']) < time() - LOGIN_LOCK_MINUTES * 60;
    if ($expired) {
        q('DELETE FROM login_attempts WHERE ip = ?', [$ip]);
        return false;
    }
    return (int) $r['attempts'] >= LOGIN_MAX_ATTEMPTS;
}

function login_failed(string $ip): void
{
    q('INSERT INTO login_attempts (ip, attempts, last_at) VALUES (?, 1, NOW())
       ON DUPLICATE KEY UPDATE attempts = attempts + 1, last_at = NOW()', [$ip]);
}

function login_succeeded(string $ip, array $admin): void
{
    q('DELETE FROM login_attempts WHERE ip = ?', [$ip]);
    q('UPDATE admins SET last_login_at = NOW() WHERE id = ?', [$admin['id']]);
    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int) $admin['id'];
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

/* ---------- uploads ---------- */

/**
 * Stores an uploaded image under uploads/YYYY/MM and returns its site-relative path.
 * Returns null when no file was sent; throws on an invalid file.
 */
function store_upload(?array $file): ?string
{
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('آپلود فایل ناموفق بود (کد خطا ' . $file['error'] . ').');
    }
    if ($file['size'] > UPLOAD_MAX_BYTES) {
        throw new RuntimeException('حجم تصویر نباید بیشتر از ۸ مگابایت باشد.');
    }
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset($allowed[$mime]) || @getimagesize($file['tmp_name']) === false) {
        throw new RuntimeException('فقط تصویر JPG، PNG، GIF یا WEBP مجاز است.');
    }
    $dir = UPLOAD_DIR . '/' . date('Y/m');
    $abs = ROOT_PATH . '/' . $dir;
    if (!is_dir($abs) && !mkdir($abs, 0755, true)) {
        throw new RuntimeException('پوشه uploads قابل نوشتن نیست.');
    }
    $name = bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], $abs . '/' . $name)) {
        throw new RuntimeException('ذخیره فایل روی هاست ناموفق بود.');
    }
    return $dir . '/' . $name;
}

/** Normalises $_FILES['x'] with multiple files into a list of single-file arrays. */
function upload_list(string $key): array
{
    if (empty($_FILES[$key]['name']) || !is_array($_FILES[$key]['name'])) {
        return [];
    }
    $out = [];
    foreach ($_FILES[$key]['name'] as $i => $name) {
        $out[] = [
            'name'     => $name,
            'type'     => $_FILES[$key]['type'][$i],
            'tmp_name' => $_FILES[$key]['tmp_name'][$i],
            'error'    => $_FILES[$key]['error'][$i],
            'size'     => $_FILES[$key]['size'][$i],
        ];
    }
    return $out;
}

function image_size(string $path): array
{
    $info = @getimagesize(ROOT_PATH . '/' . $path);
    return $info ? [(int) $info[0], (int) $info[1]] : [1920, 1080];
}

function unread_count(): int
{
    return (int) row('SELECT COUNT(*) AS c FROM messages WHERE is_read = 0')['c'];
}
