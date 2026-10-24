<?php
/**
 * SEO helpers: canonical site URL, meta text, structured data and sitemap/robots/feed support.
 */

declare(strict_types=1);

/**
 * Base URL of the site without a trailing slash. The "site_url" setting wins (set it in the admin so
 * canonical links never depend on the host name a visitor typed); otherwise it is read from the request.
 */
function site_url(): string
{
    $configured = trim(setting('site_url'));
    if ($configured !== '' && preg_match('~^https?://[^\s/?#]+(/[^\s?#]*)?$~i', $configured)) {
        return rtrim($configured, '/');
    }
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
    $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
    if (!preg_match('~^[A-Za-z0-9.\-]+(:\d+)?$~', $host)) {
        $host = 'localhost';
    }
    $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    return ($https ? 'https' : 'http') . '://' . $host . $dir;
}

/** Absolute URL of a site path ("" is the home page). */
function url_for(string $path = ''): string
{
    return site_url() . '/' . ltrim($path, '/');
}

/** Plain text of an HTML snippet, cut at a word boundary (meta descriptions stay near 155 characters). */
function meta_text(?string $html, int $max = 155): string
{
    $text = html_entity_decode(strip_tags(str_replace(['</p>', '</li>', '<br>', '<br/>', '<br />'], ' ', (string) $html)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = trim((string) preg_replace('~\s+~u', ' ', str_replace("\u{200C}\u{200C}", "\u{200C}", $text)));
    if (mb_strlen($text) <= $max) {
        return $text;
    }
    $cut = mb_substr($text, 0, $max);
    $space = mb_strrpos($cut, ' ');
    if ($space !== false && $space > $max * 0.6) {
        $cut = mb_substr($cut, 0, $space);
    }
    return preg_replace('~[\s,.;:،؛\-–]+$~u', '', $cut) . '…';
}

/** Jalali -> Gregorian, the inverse of gregorian_to_jalali(). */
function jalali_to_gregorian(int $jy, int $jm, int $jd): array
{
    $jy += 1595;
    $days = -355668 + (365 * $jy) + intdiv($jy, 33) * 8 + intdiv(($jy % 33) + 3, 4) + $jd
        + ($jm < 7 ? ($jm - 1) * 31 : (($jm - 7) * 30) + 186);
    $gy = 400 * intdiv($days, 146097);
    $days %= 146097;
    if ($days > 36524) {
        $days--;
        $gy += 100 * intdiv($days, 36524);
        $days %= 36524;
        if ($days >= 365) {
            $days++;
        }
    }
    $gy += 4 * intdiv($days, 1461);
    $days %= 1461;
    if ($days > 365) {
        $gy += intdiv($days - 1, 365);
        $days = ($days - 1) % 365;
    }
    $gd = $days + 1;
    $leap = ($gy % 4 === 0 && $gy % 100 !== 0) || $gy % 400 === 0;
    $lengths = [0, 31, $leap ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    $gm = 1;
    while ($gm < 13 && $gd > $lengths[$gm]) {
        $gd -= $lengths[$gm];
        $gm++;
    }
    return [$gy, $gm, $gd];
}

/** "۳۰ مهر ۱۴۰۵" or "05 فروردین 1405" -> "2026-10-22"; null when the text is not a Jalali date. */
function jalali_text_to_iso(string $text): ?string
{
    $text = strtr($text, ['۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9']);
    $months = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
    if (!preg_match('~(\d{1,2})\s+(\S+)\s+(\d{4})~u', $text, $m)) {
        return null;
    }
    $month = array_search($m[2], $months, true);
    if ($month === false) {
        return null;
    }
    [$gy, $gm, $gd] = jalali_to_gregorian((int) $m[3], $month + 1, (int) $m[1]);
    return sprintf('%04d-%02d-%02d', $gy, $gm, $gd);
}

/** MySQL datetime -> ISO 8601 with the site's time zone offset. */
function iso_datetime(?string $mysql): ?string
{
    if (!$mysql) {
        return null;
    }
    $t = strtotime($mysql);
    return $t ? date('c', $t) : null;
}

/** <script type="application/ld+json"> for a list of schema.org nodes. */
function json_ld(array $nodes): string
{
    $nodes = array_values(array_filter($nodes));
    if (!$nodes) {
        return '';
    }
    $json = json_encode(['@context' => 'https://schema.org', '@graph' => $nodes], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP);
    return '<script type="application/ld+json">' . $json . '</script>';
}

/** BreadcrumbList node; $items is [[name, absolute url], ...] starting at the home page. */
function schema_breadcrumb(array $items): array
{
    $list = [];
    foreach (array_values($items) as $i => [$name, $url]) {
        $list[] = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $name, 'item' => $url];
    }
    return ['@type' => 'BreadcrumbList', 'itemListElement' => $list];
}

/** The site owner as a schema.org Person plus the WebSite, shared by every page. */
function schema_site_nodes(array $socials): array
{
    $base = site_url();
    $name = setting('profile_name');
    $same = [];
    foreach ($socials as $social) {
        if (preg_match('~^https?://~i', (string) $social['url'])) {
            $same[] = $social['url'];
        }
    }
    $knows = array_column(rows('SELECT text FROM skills ORDER BY sort, id LIMIT 12'), 'text');
    $avatar = setting('profile_avatar');

    $person = [
        '@type'         => 'Person',
        '@id'           => $base . '/#person',
        'name'          => $name,
        'alternateName' => setting('site_author'),
        'url'           => $base . '/',
        'jobTitle'      => trim(setting('profile_badge'), " !"),
        'description'   => setting('site_description'),
    ];
    if ($avatar !== '') {
        $person['image'] = absolute_url($avatar);
    }
    if ($same) {
        $person['sameAs'] = $same;
    }
    if ($knows) {
        $person['knowsAbout'] = $knows;
    }

    return [
        $person,
        [
            '@type'      => 'WebSite',
            '@id'        => $base . '/#website',
            'url'        => $base . '/',
            'name'       => $name,
            'inLanguage' => 'fa-IR',
            'publisher'  => ['@id' => $base . '/#person'],
        ],
    ];
}

/** Plain text of heading_html ("title<br/>subtitle" becomes "title - subtitle"). */
function heading_text(?string $html): string
{
    $text = preg_replace('~<br\s*/?>~i', ' - ', (string) $html);
    return trim((string) preg_replace('~\s+~u', ' ', html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
}

/**
 * Nodes for an inner page: the WebPage itself (type is WebPage, CollectionPage, ContactPage, ProfilePage ...)
 * and its breadcrumb trail. $trail lists the crumbs after the home page as [[name, path], ...].
 */
function schema_page(string $type, string $path, string $name, string $description, array $trail = [], array $extra = []): array
{
    $base = site_url();
    $crumbs = [[setting('profile_name'), $base . '/']];
    foreach ($trail as [$crumbName, $crumbPath]) {
        $crumbs[] = [$crumbName, url_for($crumbPath)];
    }
    $page = array_merge([
        '@type'       => $type,
        '@id'         => url_for($path) . '#webpage',
        'url'         => url_for($path),
        'name'        => $name,
        'description' => $description,
        'inLanguage'  => 'fa-IR',
        'isPartOf'    => ['@id' => $base . '/#website'],
        'about'       => ['@id' => $base . '/#person'],
    ], $extra);
    if (count($crumbs) > 1) {
        $page['breadcrumb'] = ['@id' => url_for($path) . '#breadcrumb'];
        return [$page, ['@id' => url_for($path) . '#breadcrumb'] + schema_breadcrumb($crumbs)];
    }
    return [$page];
}

/**
 * Sends visitors who arrive on another host name (the old domain, www., ...) to the address saved as "site_url",
 * so search engines only ever see one version of each page. Does nothing on localhost or IP addresses,
 * for non-GET requests, or while no site URL is configured. Call it before any output.
 */
function redirect_to_canonical_host(): void
{
    $parts = parse_url(trim(setting('site_url')));
    if (!is_array($parts) || empty($parts['host']) || !empty($parts['path']) && $parts['path'] !== '/') {
        return;
    }
    if (!in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true)) {
        return;
    }
    $host = strtolower((string) preg_replace('~:\d+$~', '', (string) ($_SERVER['HTTP_HOST'] ?? '')));
    if ($host === '' || $host === strtolower($parts['host']) || strpos($host, '.') === false || filter_var($host, FILTER_VALIDATE_IP)) {
        return;
    }
    $target = ($parts['scheme'] ?? 'https') . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');
    header('Location: ' . $target . ($_SERVER['REQUEST_URI'] ?? '/'), true, 301);
    exit;
}
