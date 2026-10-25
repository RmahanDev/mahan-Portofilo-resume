<?php
/**
 * Small helper layer: database access, settings, escaping and URL helpers.
 */

declare(strict_types=1);

// The host may run PHP 7.4 (INSTALL.md says 7.4 or newer): provide the PHP 8 string helpers when they are missing.
if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool
    {
        return $needle === '' || strpos($haystack, $needle) !== false;
    }
}
if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool
    {
        return $needle === '' || strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}
if (!function_exists('str_ends_with')) {
    function str_ends_with(string $haystack, string $needle): bool
    {
        return $needle === '' || substr($haystack, -strlen($needle)) === $needle;
    }
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $c = $GLOBALS['CONFIG']['db'];
        $pdo = db_connect($c['host'], $c['name'], $c['user'], $c['pass'], $c['charset'] ?? 'utf8mb4');
    }
    return $pdo;
}

function db_connect(string $host, string $name, string $user, string $pass, string $charset = 'utf8mb4'): PDO
{
    $dsn = "mysql:host={$host};dbname={$name};charset={$charset}";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        // Some hosts default to unbuffered results, which makes a second query fail while a result is half read.
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
    ]);
    // Keep NOW() in MySQL on the same clock as PHP (Asia/Tehran), whatever the server's zone is.
    $pdo->exec("SET time_zone = '" . date('P') . "'");
    return $pdo;
}

function q(string $sql, array $params = []): PDOStatement
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

function rows(string $sql, array $params = []): array
{
    return q($sql, $params)->fetchAll();
}

function row(string $sql, array $params = []): ?array
{
    $stmt = q($sql, $params);
    $r = $stmt->fetch();
    $stmt->closeCursor();
    return $r === false ? null : $r;
}

/** All site settings as key => value, loaded once per request ($refresh re-reads them). */
function settings(bool $refresh = false): array
{
    static $all = null;
    if ($all === null || $refresh) {
        $all = [];
        foreach (rows('SELECT k, v FROM settings') as $r) {
            $all[$r['k']] = $r['v'];
        }
    }
    return $all;
}

function setting(string $key, string $default = ''): string
{
    return settings()[$key] ?? $default;
}

function save_setting(string $key, string $value): void
{
    q('INSERT INTO settings (k, v) VALUES (?, ?) ON DUPLICATE KEY UPDATE v = VALUES(v)', [$key, $value]);
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Echo-escaped setting, the most common thing a template does. */
function s(string $key): string
{
    return e(setting($key));
}

/**
 * URL for a stored file path. Paths are stored relative to the site root
 * ("assets/..." or "uploads/..."); pages one level deep pass "../".
 */
function asset(?string $path, string $prefix = ''): string
{
    $path = (string) $path;
    if ($path === '' || preg_match('~^(https?:)?//|^data:|^/~i', $path)) {
        return e($path);
    }
    return e($prefix . $path);
}

/** Prefix site-relative src/href attributes inside admin-written HTML. */
function rich(?string $html, string $prefix = ''): string
{
    $html = (string) $html;
    if ($prefix === '') {
        return $html;
    }
    return preg_replace('~\b(src|href)=(["\'])(assets/|uploads/)~i', '$1=$2' . $prefix . '$3', $html);
}

/** Latin digits to Persian digits. */
function fa_digits($value): string
{
    return strtr((string) $value, ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹']);
}

/** Minutes needed to read an HTML text (about 180 words a minute in Persian, never below one). */
function reading_minutes(string $html): int
{
    $words = preg_split('~\s+~u', trim(strip_tags($html)), -1, PREG_SPLIT_NO_EMPTY);
    return max(1, (int) ceil(count($words) / 180));
}

/** Absolute URL of a site file (og:image and similar tags need one). */
function absolute_url(string $path): string
{
    return site_url() . '/' . ltrim($path, '/');
}

function slugify(string $text): string
{
    $text = trim(mb_strtolower($text));
    $text = preg_replace('~[^\p{L}\p{N}]+~u', '-', $text);
    return trim($text, '-') ?: 'item-' . substr(bin2hex(random_bytes(3)), 0, 6);
}

function client_ip(): string
{
    return substr($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0', 0, 45);
}

function not_found(string $prefix = ''): void
{
    http_response_code(404);
    $GLOBALS['NOT_FOUND_PREFIX'] = $prefix;
    require __DIR__ . '/views/404.php';
    exit;
}

/**
 * Published projects related to a project: the ones sharing the most categories come first, the rest fill up the list.
 * With $projectId = 0 (a blog post asking for work samples) it is simply the newest projects.
 */
function related_projects(int $projectId, int $limit = 3): array
{
    $limit = max(1, $limit);
    if ($projectId > 0) {
        $found = rows(
            'SELECT p.id, p.slug, p.title, p.cover, p.card_category, COUNT(pc.category_id) AS shared
             FROM projects p
             LEFT JOIN project_categories pc ON pc.project_id = p.id
                  AND pc.category_id IN (SELECT category_id FROM project_categories WHERE project_id = ?)
             WHERE p.is_published = 1 AND p.id <> ?
             GROUP BY p.id, p.slug, p.title, p.cover, p.card_category, p.sort
             ORDER BY shared DESC, p.sort, p.id
             LIMIT ' . $limit,
            [$projectId, $projectId]
        );
    } else {
        $found = rows('SELECT id, slug, title, cover, card_category FROM projects WHERE is_published = 1 ORDER BY id DESC LIMIT ' . $limit);
    }
    // "label" is what a card shows under the title: the typed label, otherwise the primary category.
    $names = project_category_names();
    foreach ($found as &$p) {
        $p['label'] = $p['card_category'] !== '' ? $p['card_category'] : ($names[(int) ($p['id'] ?? 0)][0] ?? '');
    }
    unset($p);
    return $found;
}

/** Newest published blog posts, optionally leaving one out. */
function latest_posts(int $limit = 2, int $exceptId = 0): array
{
    return rows(
        'SELECT slug, title, category, date_text, cover, excerpt FROM posts WHERE is_published = 1 AND id <> ? ORDER BY sort, id DESC LIMIT ' . max(1, $limit),
        [$exceptId]
    );
}

/** Category names of every project, as project id => [name, ...], primary (lowest sort) first. */
function project_category_names(): array
{
    $names = [];
    foreach (rows('SELECT pc.project_id, c.name FROM project_categories pc JOIN categories c ON c.id = pc.category_id ORDER BY c.sort, c.id') as $r) {
        $names[(int) $r['project_id']][] = $r['name'];
    }
    return $names;
}
