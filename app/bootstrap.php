<?php
/**
 * Shared bootstrap for the public site and the admin panel.
 */

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_FILE', ROOT_PATH . '/config/config.php');
define('UPLOAD_DIR', 'uploads');
// Bump when a stylesheet/script changes so browsers fetch the new file after a deploy.
define('ASSET_VERSION', '5');

mb_internal_encoding('UTF-8');
date_default_timezone_set('Asia/Tehran');

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/jalali.php';
require_once __DIR__ . '/seo.php';

if (!is_file(CONFIG_FILE)) {
    // Fresh host: send the visitor to the web installer.
    $depth = $GLOBALS['ROOT_DEPTH'] ?? 0;
    header('Location: ' . str_repeat('../', $depth) . 'install/');
    exit;
}

$GLOBALS['CONFIG'] = require CONFIG_FILE;

if (!empty($GLOBALS['CONFIG']['debug'])) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

// Bring an older installed database up to date after a deploy (no-op when nothing is pending).
require_once __DIR__ . '/migrate.php';
auto_migrate();
