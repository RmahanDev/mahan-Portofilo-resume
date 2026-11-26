<?php
/**
 * Page view tracking for the public site and the helpers the admin statistics page uses.
 *
 * A view is counted once per visitor and page every 30 minutes, so refreshing a page does not inflate the numbers.
 * Bots, link previews, prefetches and the logged-in admin are not counted. Tracking errors are swallowed:
 * statistics must never take a page down.
 */

declare(strict_types=1);

const ANALYTICS_REPEAT_MINUTES = 30;
const ANALYTICS_KEEP_DAYS = 180;

function analytics_is_bot(string $ua): bool
{
    return $ua === '' || (bool) preg_match(
        '~bot|crawl|spider|slurp|archiver|facebookexternalhit|embedly|preview|monitor|curl|wget|python|java/|go-http|okhttp|headless|lighthouse|pingdom|uptime|scan~i',
        $ua
    );
}

function analytics_device(string $ua): string
{
    if (preg_match('~ipad|tablet|kindle|silk|android(?!.*mobile)~i', $ua)) {
        return 'tablet';
    }
    if (preg_match('~mobi|iphone|ipod|android|blackberry|opera mini|iemobile~i', $ua)) {
        return 'mobile';
    }
    return 'desktop';
}

function analytics_browser(string $ua): string
{
    $map = [
        '~Edg(e|A|iOS)?/~'          => 'Edge',
        '~OPR/|Opera~'              => 'Opera',
        '~SamsungBrowser~'          => 'Samsung',
        '~YaBrowser~'               => 'Yandex',
        '~Firefox|FxiOS~'           => 'Firefox',
        '~Chrome|CriOS|Chromium~'   => 'Chrome',
        '~Safari~'                  => 'Safari',
    ];
    foreach ($map as $re => $name) {
        if (preg_match($re, $ua)) {
            return $name;
        }
    }
    return 'Other';
}

function analytics_os(string $ua): string
{
    $map = [
        '~Windows~'           => 'Windows',
        '~Android~'           => 'Android',
        '~iPhone|iPad|iPod~'  => 'iOS',
        '~Mac OS X|Macintosh~' => 'macOS',
        '~CrOS~'              => 'ChromeOS',
        '~Linux~'             => 'Linux',
    ];
    foreach ($map as $re => $name) {
        if (preg_match($re, $ua)) {
            return $name;
        }
    }
    return 'Other';
}

/** Host name of an external referrer ("" for direct visits and links inside the site). */
function analytics_referrer(): string
{
    $host = strtolower((string) parse_url((string) ($_SERVER['HTTP_REFERER'] ?? ''), PHP_URL_HOST));
    $host = (string) preg_replace('~^www\.~', '', $host);
    if ($host === '') {
        return '';
    }
    $own = [
        (string) preg_replace('~^www\.~', '', strtolower((string) preg_replace('~:\d+$~', '', (string) ($_SERVER['HTTP_HOST'] ?? '')))),
        (string) preg_replace('~^www\.~', '', strtolower((string) parse_url(setting('site_url'), PHP_URL_HOST))),
    ];
    return in_array($host, $own, true) ? '' : substr($host, 0, 190);
}

/**
 * Records a view of the current page. $type is page|post|project; $refId is the post or project id.
 * Runs at most once per request. Returns true when the view was counted.
 */
function track_visit(string $type = 'page', int $refId = 0): bool
{
    static $done = false;
    if ($done) {
        return false;
    }
    $done = true;

    try {
        if (!in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET'], true) || !empty($_COOKIE['pf_admin'])) {
            return false;
        }
        $ua = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 400);
        $purpose = strtolower((string) ($_SERVER['HTTP_SEC_PURPOSE'] ?? $_SERVER['HTTP_PURPOSE'] ?? $_SERVER['HTTP_X_MOZ'] ?? ''));
        if (analytics_is_bot($ua) || strpos($purpose, 'prefetch') !== false || strpos($purpose, 'prerender') !== false) {
            return false;
        }

        $ip = client_ip();
        $visitor = substr(hash('sha256', $ip . '|' . $ua . '|' . setting('analytics_salt', 'pf')), 0, 32);
        $path = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
        $path = mb_substr(rawurldecode($path !== '' ? $path : '/'), 0, 255);
        $now = time();

        $seen = row(
            'SELECT id FROM visits WHERE visitor = ? AND path = ? AND created_at > ? LIMIT 1',
            [$visitor, $path, date('Y-m-d H:i:s', $now - ANALYTICS_REPEAT_MINUTES * 60)]
        );
        if ($seen) {
            return false;
        }

        q(
            'INSERT INTO visits (visitor, ip, path, page_type, ref_id, referrer, device, browser, os, user_agent, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [$visitor, $ip, $path, $type, $refId, analytics_referrer(), analytics_device($ua), analytics_browser($ua), analytics_os($ua), $ua, date('Y-m-d H:i:s', $now)]
        );
        // "updated_at = updated_at" keeps the ON UPDATE timestamp (used as dateModified for SEO) untouched.
        if ($type === 'post' && $refId > 0) {
            q('UPDATE posts SET views = views + 1, updated_at = updated_at WHERE id = ?', [$refId]);
        } elseif ($type === 'project' && $refId > 0) {
            q('UPDATE projects SET views = views + 1, updated_at = updated_at WHERE id = ?', [$refId]);
        }
        if (mt_rand(1, 200) === 1) {
            q('DELETE FROM visits WHERE created_at < ?', [date('Y-m-d H:i:s', $now - ANALYTICS_KEEP_DAYS * 86400)]);
        }
        return true;
    } catch (Throwable $e) {
        return false;
    }
}

/** View count formatted for display: Persian digits with a thousands separator. */
function views_text($count): string
{
    return fa_digits(number_format((int) $count, 0, '', '٬'));
}
