<?php
// AJAX endpoint for the contact form: validates and stores the message.
require __DIR__ . '/app/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

function reply(int $status, string $message): void
{
    http_response_code($status);
    echo json_encode(['message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    reply(405, 'Method not allowed');
}

$field = static fn(string $k, int $max): string => mb_substr(trim((string) ($_POST[$k] ?? '')), 0, $max);

$name    = $field('nameContact', 200);
$email   = $field('emailContact', 200);
$phone   = $field('phoneContact', 50);
$subject = $field('subjectContact', 300);
$message = $field('messageContact', 5000);

// Bots fill the hidden "website" field or submit instantly; pretend success.
$ts = (int) ($_POST['ts'] ?? 0);
if ($field('website', 200) !== '' || ($ts > 0 && time() - $ts < 3)) {
    reply(200, setting('contact_success_text'));
}

if ($name === '' || $subject === '' || $message === '') {
    reply(422, 'لطفا همه فیلدهای ضروری را پر کنید.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    reply(422, 'ایمیل نادرست است.');
}

$ip = client_ip();
$recent = (int) row(
    'SELECT COUNT(*) AS c FROM messages WHERE ip = ? AND created_at > (NOW() - INTERVAL 1 HOUR)',
    [$ip]
)['c'];
if ($recent >= 5) {
    reply(429, 'تعداد پیام‌های ارسالی شما زیاد است، لطفا کمی بعد دوباره تلاش کنید.');
}

q(
    'INSERT INTO messages (name, email, phone, subject, message, ip) VALUES (?, ?, ?, ?, ?, ?)',
    [$name, $email, $phone, $subject, $message, $ip]
);

reply(200, setting('contact_success_text'));
