<?php
// AJAX image upload used by the HTML editor's "insert image" button.
require dirname(__DIR__) . '/app/admin/core.php';

header('Content-Type: application/json; charset=utf-8');

if (!current_admin()) {
    http_response_code(401);
    exit(json_encode(['error' => 'ابتدا وارد شوید.'], JSON_UNESCAPED_UNICODE));
}
verify_post();

try {
    $path = store_upload($_FILES['file'] ?? null);
    if ($path === null) {
        throw new RuntimeException('فایلی انتخاب نشده است.');
    }
    echo json_encode(['path' => $path], JSON_UNESCAPED_UNICODE);
} catch (RuntimeException $ex) {
    http_response_code(422);
    echo json_encode(['error' => $ex->getMessage()], JSON_UNESCAPED_UNICODE);
}
