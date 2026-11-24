<?php
/**
 * Site theme: colors, fonts and favicon editable from the admin panel ("تم و رنگ‌ها").
 *
 * Everything is stored in the `settings` table, so no schema change is needed:
 *   theme_c_<color>              global color override        (e.g. theme_c_accent = #ff5500)
 *   theme_s_<section>_<color>    override inside one section  (e.g. theme_s_sidebar_surface1)
 *   theme_font_body / _heading   path of an uploaded font file (uploads/fonts/...)
 *   theme_favicon_mode           '' (default) | 'raster' | 'single'; theme_favicon_v is a cache-busting stamp
 *   theme_apply_admin            1 = also theme the admin panel
 *
 * With nothing stored, nothing is printed and the site looks exactly like the stylesheets say.
 * The CSS files only read CSS variables (see :root in rtl.css / enhance.css); theme_css() prints an
 * inline <style> after them that re-declares those variables, globally on :root and per section.
 */

declare(strict_types=1);

const THEME_FONT_DIR = 'uploads/fonts';
const THEME_FAVICON_DIR = 'uploads/favicon';
const THEME_FONT_MAX_BYTES = 6 * 1024 * 1024;
const THEME_FAVICON_MAX_BYTES = 4 * 1024 * 1024;
const THEME_FONT_FORMATS = ['woff2' => 'woff2', 'woff' => 'woff', 'ttf' => 'truetype', 'otf' => 'opentype'];

/** Global colors: key => [label, default, where it is used]. Grouped for the admin form. */
function theme_palette(): array
{
    return [
        'سطح‌ها و پس‌زمینه‌ها' => [
            'bg'       => ['پس‌زمینه کل سایت', '#121212', 'پشت همهٔ صفحه‌ها، لودینگ و ناحیهٔ اسکرول'],
            'surface1' => ['پس‌زمینه باکس‌ها', '#1e1e1f', 'سایدبار، باکس اصلی محتوا، کارت‌ها و پاپ‌آپ‌ها'],
            'surface2' => ['پس‌زمینه ثانویه', '#2a2a2b', 'نوار منو، برچسب‌ها، منوی کشویی و بلوک‌های کد'],
            'surface3' => ['پس‌زمینه دکمه‌ها و آیکون‌ها', '#202021', 'داخل دکمه‌ها و قاب آیکون‌ها'],
            'overlay'  => ['لایهٔ تیره روی تصاویر', '#0d0d0d', 'روی تصویر نمونه‌کارها و پس‌زمینهٔ گالری'],
        ],
        'متن‌ها' => [
            'heading'   => ['تیترها و متن پررنگ', '#fcfcfc', 'عنوان‌ها، نام، لینک‌های سایدبار'],
            'paragraph' => ['متن معمولی', '#d6d6d6', 'پاراگراف‌ها، منو و توضیحات'],
            'muted'     => ['متن کم‌رنگ', '#9a9a9b', 'تاریخ‌ها، متن‌های کمکی و برچسب‌های ریز'],
            'onaccent'  => ['متن روی رنگ اصلی', '#121212', 'متن داخل دایره‌ها و نشان‌هایی که زمینهٔ رنگی دارند'],
        ],
        'رنگ اصلی (برند)' => [
            'accent'  => ['رنگ اصلی', '#ffdb6e', 'لینک‌ها، منوی فعال، دکمه‌ها، آیکون‌ها و برجسته‌سازی‌ها'],
            'accent2' => ['رنگ دوم گرادینت', '#ffbc5e', 'انتهای گرادینت رنگ اصلی (نوار پیشرفت، منوی فعال، خط تیتر)'],
        ],
        'حاشیه‌ها و خطوط' => [
            'border' => ['حاشیه‌ها', '#383838', 'خط دور باکس‌ها، جداکننده‌ها و تایم‌لاین'],
            'line'   => ['خطوط نازک و درخشش', '#ffffff', 'خطوط محو جدول‌ها، شبکهٔ لودینگ و درخشش'],
            'scroll' => ['نوار اسکرول', '#333334', 'دستگیرهٔ نوار پیمایش'],
        ],
        'وضعیت‌ها' => [
            'error'   => ['خطا', '#b84c4c', 'پیام‌های خطا و فیلدهای نامعتبر'],
            'success' => ['موفقیت / آنلاین', '#4ade80', 'نقطهٔ «آمادهٔ همکاری» و نشان‌های سبز'],
            'warning' => ['هشدار', '#ff8064', 'کادرهای هشدار داخل مقاله‌ها'],
        ],
    ];
}

/** Flat key => default list of the palette. */
function theme_defaults(): array
{
    $out = [];
    foreach (theme_palette() as $fields) {
        foreach ($fields as $key => $def) {
            $out[$key] = $def[1];
        }
    }
    return $out;
}

function theme_color_label(string $key): string
{
    foreach (theme_palette() as $fields) {
        if (isset($fields[$key])) {
            return $fields[$key][0];
        }
    }
    return $key;
}

/**
 * Sections of the site whose colors can be changed on their own.
 * 'selector' is the element(s) the colors apply to (children inherit them); 'keys' are the palette
 * colors the section uses. A color left empty follows the global palette.
 */
function theme_sections(): array
{
    return [
        'sidebar'   => ['label' => 'سایدبار (پروفایل و اطلاعات تماس)', 'selector' => '.sidebar',
            'keys' => ['surface1', 'surface2', 'surface3', 'border', 'heading', 'paragraph', 'accent', 'accent2']],
        'nav'       => ['label' => 'منوی بالای صفحه', 'selector' => '.nav-container',
            'keys' => ['surface2', 'border', 'heading', 'paragraph', 'accent', 'accent2']],
        'content'   => ['label' => 'باکس اصلی محتوا', 'selector' => '.box-outer:not(.sidebar)',
            'keys' => ['surface1', 'border', 'heading', 'paragraph', 'accent', 'accent2']],
        'buttons'   => ['label' => 'دکمه‌ها', 'selector' => '.btn',
            'keys' => ['surface3', 'border', 'heading', 'accent', 'accent2']],
        'about'     => ['label' => 'صفحه درباره من (خدمات، تجربه کاری، اسلایدر)', 'selector' => '.case-item, .review-item, .testimonials, .swiper-pagination',
            'keys' => ['surface1', 'surface2', 'border', 'heading', 'paragraph', 'accent', 'accent2']],
        'resume'    => ['label' => 'صفحه رزومه (تایم‌لاین و مهارت‌ها)', 'selector' => '.timeline, .progress, .progress-bar, .box-avatar',
            'keys' => ['surface2', 'border', 'heading', 'paragraph', 'accent', 'accent2', 'onaccent']],
        'portfolio' => ['label' => 'صفحه نمونه کارها (فیلتر و کارت‌ها)', 'selector' => '.filter, .select, .gallery-grid, .placeholder',
            'keys' => ['surface1', 'surface2', 'border', 'heading', 'paragraph', 'accent', 'accent2', 'overlay']],
        'blog'      => ['label' => 'وبلاگ و صفحهٔ مقاله / پروژه', 'selector' => '.news-item, .header-post, .header-project, .caption-post, .post-meta, .footer-post, .related-posts, .link-card, .info-box, .fact, .download-box, .callout',
            'keys' => ['surface1', 'surface2', 'border', 'heading', 'paragraph', 'muted', 'accent', 'accent2', 'onaccent', 'warning']],
        'contact'   => ['label' => 'صفحه تماس (فرم و نقشه)', 'selector' => '.contact-form, .map-links',
            'keys' => ['surface2', 'border', 'heading', 'paragraph', 'accent', 'error']],
        'popup'     => ['label' => 'پاپ‌آپ‌ها و نقشه', 'selector' => '.popup, .mfp-content, .mapboxgl-popup-content',
            'keys' => ['surface1', 'surface2', 'border', 'heading', 'paragraph', 'accent']],
        'loading'   => ['label' => 'لودینگ ورودی و انتقال صفحه', 'selector' => '.preloader, .page-curtain',
            'keys' => ['bg', 'heading', 'accent', 'accent2', 'line']],
        'scrolltop' => ['label' => 'نوار پیشرفت اسکرول و دکمهٔ بازگشت به بالا', 'selector' => '.scroll-line, .back-to-top',
            'keys' => ['accent', 'accent2']],
    ];
}

/* ---------- color math ---------- */

/** "#abc" / "abc" / "#AABBCC" => "#aabbcc", or null when it is not a color. */
function theme_hex($value): ?string
{
    $v = ltrim(strtolower(trim((string) $value)), '#');
    if (preg_match('~^[0-9a-f]{3}$~', $v)) {
        $v = $v[0] . $v[0] . $v[1] . $v[1] . $v[2] . $v[2];
    }
    return preg_match('~^[0-9a-f]{6}$~', $v) ? '#' . $v : null;
}

/** @return int[] [r, g, b] */
function theme_rgb_parts(string $hex): array
{
    return [hexdec(substr($hex, 1, 2)), hexdec(substr($hex, 3, 2)), hexdec(substr($hex, 5, 2))];
}

function theme_rgb(string $hex): string
{
    return implode(', ', theme_rgb_parts($hex));
}

/** Mixes $hex towards $towards by $t (0..1). */
function theme_mix(string $hex, string $towards, float $t): string
{
    $a = theme_rgb_parts($hex);
    $b = theme_rgb_parts($towards);
    $out = '#';
    for ($i = 0; $i < 3; $i++) {
        $out .= str_pad(dechex((int) round($a[$i] + ($b[$i] - $a[$i]) * $t)), 2, '0', STR_PAD_LEFT);
    }
    return $out;
}

/**
 * Every CSS variable the stylesheets read, computed from a full palette (key => hex).
 * The gradients use the same recipes as the original :root block in style.css / rtl.css.
 *
 * @return array<string,string> variable name => value
 */
function theme_vars(array $c): array
{
    $a = $c['accent'];
    $a2 = $c['accent2'];
    $ar = theme_rgb($a);
    $a2r = theme_rgb($a2);
    $s3 = $c['surface3'];
    $borderLight = theme_mix($c['border'], '#ffffff', 0.04);
    $borderDark = theme_mix($c['border'], '#000000', 0.14);
    $btnLight = theme_mix($s3, '#ffffff', 0.06);
    $btnDark = theme_mix($s3, '#000000', 0.15);

    return [
        '--color-bg'            => $c['bg'],
        '--color-surface-1'     => $c['surface1'],
        '--color-surface-2'     => $c['surface2'],
        '--color-surface-3'     => $s3,
        '--color-border'        => $c['border'],
        '--color-heading'       => $c['heading'],
        '--color-paragraph'     => $c['paragraph'],
        '--color-muted'         => $c['muted'],
        '--color-accent'        => $a,
        '--color-accent-2'      => $a2,
        '--color-on-accent'     => $c['onaccent'],
        '--color-error'         => $c['error'],
        '--color-error-text'    => theme_mix($c['error'], '#ffffff', 0.5),
        '--color-success'       => $c['success'],
        '--color-warning'       => $c['warning'],
        '--color-warning-text'  => theme_mix($c['warning'], '#ffffff', 0.22),
        '--color-line'          => $c['line'],
        '--color-overlay'       => $c['overlay'],
        '--color-scroll'        => $c['scroll'],
        '--color-scroll-hover'  => theme_mix($c['scroll'], '#ffffff', 0.1),
        '--color-accent-rgb'    => $ar,
        '--color-accent-2-rgb'  => $a2r,
        '--color-success-rgb'   => theme_rgb($c['success']),
        '--color-warning-rgb'   => theme_rgb($c['warning']),
        '--color-error-rgb'     => theme_rgb($c['error']),
        '--color-line-rgb'      => theme_rgb($c['line']),
        '--color-overlay-rgb'   => theme_rgb($c['overlay']),
        '--color-heading-rgb'   => theme_rgb($c['heading']),
        '--color-surface-1-rgb' => theme_rgb($c['surface1']),
        '--color-surface-2-rgb' => theme_rgb($c['surface2']),
        '--color-surface-3-rgb' => theme_rgb($s3),
        '--color-gradient'             => "linear-gradient(137.84deg, {$a} 26.31%, {$a2} 93.75%)",
        '--color-gradient-soft'        => "linear-gradient(135deg, rgba({$ar}, 0.0375) 0%, rgba({$a2r}, 0) 100%), {$c['surface1']}",
        '--color-gradient-text'        => "linear-gradient(90deg, {$a}, {$a2})",
        '--color-gradient-border'      => "linear-gradient(-155deg, {$a} 0%, rgba({$a2r}, 0) 50%)",
        '--color-gradient-border-gray' => "linear-gradient(135deg, {$borderLight} 0%, rgba(" . theme_rgb($borderLight) . ", 0) 48.44%)",
        '--color-gradient-gray'        => "linear-gradient(137.84deg, {$borderLight} 2.69%, {$borderDark} 93.75%)",
        '--color-gradient-icon'        => "linear-gradient(135deg, rgba({$ar}, 0.25) 0%, rgba({$a2r}, 0) 59.86%), {$s3}",
        '--color-btn'                  => "linear-gradient(135deg, rgba(" . theme_rgb($btnLight) . ", 0.25) 0%, rgba(" . theme_rgb($btnDark) . ", 0) 100%), {$s3}",
    ];
}

function theme_declarations(array $vars): string
{
    $out = '';
    foreach ($vars as $name => $value) {
        $out .= $name . ':' . $value . ';';
    }
    return $out;
}

/* ---------- overrides (what the admin chose) ---------- */

/**
 * Stored overrides as ['c' => [color => hex], 's' => [section => [color => hex]]].
 * Anything that is not a valid hex color is ignored.
 */
function theme_overrides(?array $settings = null): array
{
    $settings = $settings ?? settings();
    $out = ['c' => [], 's' => []];
    $defaults = theme_defaults();
    $sections = theme_sections();
    foreach ($defaults as $key => $_) {
        $hex = theme_hex($settings['theme_c_' . $key] ?? '');
        if ($hex !== null) {
            $out['c'][$key] = $hex;
        }
    }
    foreach ($sections as $sec => $def) {
        foreach ($def['keys'] as $key) {
            $hex = theme_hex($settings['theme_s_' . $sec . '_' . $key] ?? '');
            if ($hex !== null) {
                $out['s'][$sec][$key] = $hex;
            }
        }
    }
    return $out;
}

/** Overrides typed in the admin form (invalid or empty values are skipped). Same shape as theme_overrides(). */
function theme_overrides_from_post(array $post): array
{
    $out = ['c' => [], 's' => []];
    foreach (theme_defaults() as $key => $_) {
        $hex = theme_hex($post['c'][$key] ?? '');
        if ($hex !== null) {
            $out['c'][$key] = $hex;
        }
    }
    foreach (theme_sections() as $sec => $def) {
        foreach ($def['keys'] as $key) {
            $hex = theme_hex($post['s'][$sec][$key] ?? '');
            if ($hex !== null) {
                $out['s'][$sec][$key] = $hex;
            }
        }
    }
    return $out;
}

/** Global palette after overrides: key => hex. */
function theme_effective(array $overrides): array
{
    return array_merge(theme_defaults(), $overrides['c'] ?? []);
}

/** One global color the way the site shows it now (for meta tags and the admin form). */
function theme_color(string $key): string
{
    $all = theme_effective(theme_overrides());
    return $all[$key] ?? '#000000';
}

/**
 * The override CSS. $root is the selector the global palette is declared on (":root" on the site,
 * ".theme-preview" in the admin preview); every section selector is prefixed with $scope.
 * Returns '' when there are no overrides at all.
 */
function theme_build_css(array $overrides, string $root = ':root', string $scope = '', bool $forceRoot = false): string
{
    $css = '';
    $global = theme_effective($overrides);
    if ($forceRoot || !empty($overrides['c'])) {
        $css .= $root . '{' . theme_declarations(theme_vars($global)) . "}\n";
    }
    foreach (theme_sections() as $sec => $def) {
        if (empty($overrides['s'][$sec])) {
            continue;
        }
        $palette = array_merge($global, $overrides['s'][$sec]);
        $selectors = array_map(
            static fn(string $s): string => ($scope !== '' ? $scope . ' ' : '') . trim($s),
            explode(',', $def['selector'])
        );
        $css .= implode(',', $selectors) . '{' . theme_declarations(theme_vars($palette)) . "}\n";
    }
    return $css;
}

/* ---------- fonts ---------- */

/** Uploaded font file for a slot ('body' | 'heading') if it still exists, otherwise ''. */
function theme_font_path(string $slot): string
{
    $path = setting('theme_font_' . $slot);
    if (!preg_match('~^' . preg_quote(THEME_FONT_DIR, '~') . '/[a-z0-9_\-]+\.(woff2|woff|ttf|otf)$~', $path)) {
        return '';
    }
    return is_file(ROOT_PATH . '/' . $path) ? $path : '';
}

function theme_font_css(string $prefix = '', bool $faceOnly = false): string
{
    $css = '';
    $stacks = [
        'body'    => ["'PF Body'", "'IRY', sans-serif"],
        'heading' => ["'PF Heading'", "'Sarb', 'IRY', sans-serif"],
    ];
    $have = [];
    foreach ($stacks as $slot => [$family, $fallback]) {
        $path = theme_font_path($slot);
        if ($path === '') {
            continue;
        }
        $have[$slot] = $family . ', ' . $fallback;
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $v = (int) @filemtime(ROOT_PATH . '/' . $path);
        $css .= '@font-face{font-family:' . $family . ';src:url("' . $prefix . $path . '?v=' . $v . '") format("' . THEME_FONT_FORMATS[$ext] . '");'
            . 'font-weight:100 900;font-style:normal;font-display:swap}' . "\n";
    }
    if ($faceOnly) {
        return $css;
    }
    if (isset($have['body'])) {
        $css .= ':root{--font-family:' . $have['body'] . '}'
            . 'body,button,input,textarea,select,.sidebar__user-name,.tooltip-inner,h3,h4,h5,.news-item .title,.header-post .title,.header-project .title,.mfp-content{font-family:' . $have['body'] . '}' . "\n";
    }
    if (isset($have['heading'])) {
        $css .= 'h1,h2,h6,.review-item .title{font-family:' . $have['heading'] . '}' . "\n";
    }
    return $css;
}

/** True when a custom body font replaces IRY (the page then should not preload IRY files). */
function theme_has_custom_body_font(): bool
{
    return theme_font_path('body') !== '';
}

/* ---------- output used by the layouts ---------- */

/** The inline <style> that applies the theme. $admin = true for the admin layouts. */
function theme_style_tag(string $prefix = '', bool $admin = false): string
{
    if ($admin && (setting('theme_apply_admin') !== '1' || isset($_GET['notheme']))) {
        return '';
    }
    $css = theme_build_css(theme_overrides()) . theme_font_css($prefix);
    return $css === '' ? '' : '    <style id="pf-theme">' . "\n" . $css . '    </style>' . "\n";
}

/** <link> tags for the favicon: the uploaded one, or the files that ship with the site. */
function theme_favicon_tags(string $prefix = '', bool $full = true): string
{
    $mode = setting('theme_favicon_mode');
    $v = setting('theme_favicon_v', '1');
    $dir = $prefix . THEME_FAVICON_DIR . '/';
    $l = static fn(string $rel, string $href, string $extra = ''): string => '    <link rel="' . $rel . '" href="' . e($href) . '"' . $extra . ">\n";

    if ($mode === 'raster' && is_file(ROOT_PATH . '/' . THEME_FAVICON_DIR . '/favicon-32x32.png')) {
        $out = $l('icon', $dir . 'favicon-32x32.png?v=' . $v, ' sizes="32x32" type="image/png"')
            . $l('icon', $dir . 'favicon-16x16.png?v=' . $v, ' sizes="16x16" type="image/png"')
            . $l('shortcut icon', $dir . 'favicon.ico?v=' . $v)
            . $l('apple-touch-icon', $dir . 'apple-touch-icon-180x180.png?v=' . $v, ' sizes="180x180"');
        return $out;
    }
    if ($mode === 'single') {
        foreach (['svg' => 'image/svg+xml', 'ico' => 'image/x-icon', 'png' => 'image/png'] as $ext => $type) {
            if (is_file(ROOT_PATH . '/' . THEME_FAVICON_DIR . '/favicon-original.' . $ext)) {
                $href = $dir . 'favicon-original.' . $ext . '?v=' . $v;
                return $l('icon', $href, ' type="' . $type . '"') . ($ext === 'ico' ? $l('shortcut icon', $href) : '')
                    . ($ext === 'png' ? $l('apple-touch-icon', $href) : '');
            }
        }
    }

    $out = $l('icon', $prefix . 'assets/images/favicons/favicon.svg', ' type="image/svg+xml"')
        . $l('icon', $prefix . 'assets/images/favicons/favicon-32x32.png', ' sizes="32x32" type="image/png"');
    if ($full) {
        $out .= $l('icon', $prefix . 'assets/images/favicons/favicon-16x16.png', ' sizes="16x16" type="image/png"');
    }
    $out .= $l('shortcut icon', $prefix . 'assets/images/favicons/favicon.ico')
        . $l('apple-touch-icon', $prefix . 'assets/images/favicons/apple-touch-icon-180x180.png', $full ? ' sizes="180x180"' : '');
    if ($full) {
        $out .= $l('apple-touch-icon', $prefix . 'assets/images/favicons/apple-touch-icon-152x152.png', ' sizes="152x152"')
            . $l('manifest', $prefix . 'assets/images/favicons/site.webmanifest');
    }
    return $out;
}

/* ---------- saving (admin only) ---------- */

function theme_remove_file(string $path): void
{
    if (preg_match('~^uploads/(fonts|favicon)/[A-Za-z0-9_\-.]+$~', $path) && is_file(ROOT_PATH . '/' . $path)) {
        @unlink(ROOT_PATH . '/' . $path);
    }
}

/**
 * Validates and stores colors. Empty values remove the override.
 *
 * @param array $posted ['c' => [key => value], 's' => [section => [key => value]]]
 * @throws RuntimeException naming the first invalid color
 */
function theme_save_colors(array $posted): void
{
    $toSave = [];
    $check = static function (string $setting, $value, string $label) use (&$toSave): void {
        $value = is_string($value) ? trim($value) : '';
        if ($value === '') {
            $toSave[$setting] = '';
            return;
        }
        $hex = theme_hex($value);
        if ($hex === null) {
            throw new RuntimeException('رنگ «' . $label . '» معتبر نیست (مثال درست: #ff8800).');
        }
        $toSave[$setting] = $hex;
    };
    foreach (theme_defaults() as $key => $_) {
        $check('theme_c_' . $key, $posted['c'][$key] ?? '', theme_color_label($key));
    }
    foreach (theme_sections() as $sec => $def) {
        foreach ($def['keys'] as $key) {
            $check('theme_s_' . $sec . '_' . $key, $posted['s'][$sec][$key] ?? '', $def['label'] . ' - ' . theme_color_label($key));
        }
    }
    foreach ($toSave as $k => $v) {
        save_setting($k, $v);
    }
}

function theme_reset_colors(): void
{
    foreach (settings() as $k => $_) {
        if (strpos($k, 'theme_c_') === 0 || strpos($k, 'theme_s_') === 0) {
            q('DELETE FROM settings WHERE k = ?', [$k]);
        }
    }
    settings(true);
}

/** Installs a font file that is already on disk. Returns its site-relative path. */
function theme_install_font(string $tmpPath, string $originalName, string $slot): string
{
    if (!in_array($slot, ['body', 'heading'], true)) {
        throw new RuntimeException('جایگاه فونت نامعتبر است.');
    }
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if (!isset(THEME_FONT_FORMATS[$ext])) {
        throw new RuntimeException('فقط فایل فونت woff2، woff، ttf یا otf مجاز است.');
    }
    $size = (int) @filesize($tmpPath);
    if ($size < 100 || $size > THEME_FONT_MAX_BYTES) {
        throw new RuntimeException('حجم فایل فونت باید بین ۱۰۰ بایت و ۶ مگابایت باشد.');
    }
    $magic = (string) file_get_contents($tmpPath, false, null, 0, 4);
    $valid = [
        'woff2' => ['wOF2'],
        'woff'  => ['wOFF'],
        'ttf'   => ["\x00\x01\x00\x00", 'true', 'ttcf'],
        'otf'   => ['OTTO', "\x00\x01\x00\x00"],
    ];
    if (!in_array($magic, $valid[$ext], true)) {
        throw new RuntimeException('این فایل یک فونت ' . strtoupper($ext) . ' سالم نیست.');
    }
    $abs = ROOT_PATH . '/' . THEME_FONT_DIR;
    if (!is_dir($abs) && !@mkdir($abs, 0755, true)) {
        throw new RuntimeException('پوشه uploads قابل نوشتن نیست.');
    }
    $name = $slot . '-' . bin2hex(random_bytes(6)) . '.' . $ext;
    if (!@copy($tmpPath, $abs . '/' . $name)) {
        throw new RuntimeException('ذخیره فایل فونت روی هاست ناموفق بود.');
    }
    $old = setting('theme_font_' . $slot);
    save_setting('theme_font_' . $slot, THEME_FONT_DIR . '/' . $name);
    if ($old !== '') {
        theme_remove_file($old);
    }
    return THEME_FONT_DIR . '/' . $name;
}

function theme_reset_font(string $slot): void
{
    $old = setting('theme_font_' . $slot);
    save_setting('theme_font_' . $slot, '');
    if ($old !== '') {
        theme_remove_file($old);
    }
}

/** Reads a PNG/JPEG/GIF/WEBP into a GD image, or null. */
function theme_gd_load(string $path)
{
    if (!function_exists('imagecreatefromstring')) {
        return null;
    }
    $img = @imagecreatefromstring((string) file_get_contents($path));
    return $img ?: null;
}

/** Square PNG of $size px (contain-fit, transparent padding). Returns PNG bytes. */
function theme_png_square($src, int $size): string
{
    $w = imagesx($src);
    $h = imagesy($src);
    $scale = min($size / $w, $size / $h);
    $nw = max(1, (int) round($w * $scale));
    $nh = max(1, (int) round($h * $scale));
    $dst = imagecreatetruecolor($size, $size);
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    imagefill($dst, 0, 0, imagecolorallocatealpha($dst, 0, 0, 0, 127));
    imagecopyresampled($dst, $src, intdiv($size - $nw, 2), intdiv($size - $nh, 2), 0, 0, $nw, $nh, $w, $h);
    ob_start();
    imagepng($dst);
    $png = (string) ob_get_clean();
    if (PHP_VERSION_ID < 80000) {
        imagedestroy($dst);
    }
    return $png;
}

/** ICO container holding PNG images ($pngs: size => bytes). */
function theme_ico(array $pngs): string
{
    ksort($pngs);
    $n = count($pngs);
    $head = pack('vvv', 0, 1, $n);
    $dir = '';
    $data = '';
    $offset = 6 + 16 * $n;
    foreach ($pngs as $size => $png) {
        $dir .= pack('CCCCvvVV', $size >= 256 ? 0 : $size, $size >= 256 ? 0 : $size, 0, 0, 1, 32, strlen($png), $offset);
        $data .= $png;
        $offset += strlen($png);
    }
    return $head . $dir . $data;
}

function theme_clear_favicon_files(): void
{
    foreach (glob(ROOT_PATH . '/' . THEME_FAVICON_DIR . '/*') ?: [] as $f) {
        if (is_file($f) && basename($f) !== '.htaccess') {
            @unlink($f);
        }
    }
}

/**
 * Installs a favicon from a file on disk.
 * PNG/JPG/GIF/WEBP: every size is generated with GD (16/32/48 ico, 180 apple, 192/512 android) when GD exists.
 * SVG and ICO (or a raster image when GD is missing) are used as they are.
 *
 * @return string 'raster' | 'single'
 */
function theme_install_favicon(string $tmpPath, string $originalName): string
{
    $size = (int) @filesize($tmpPath);
    if ($size < 20 || $size > THEME_FAVICON_MAX_BYTES) {
        throw new RuntimeException('حجم فاوآیکون باید کمتر از ۴ مگابایت باشد.');
    }
    $head = (string) file_get_contents($tmpPath, false, null, 0, 16);
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if (strncmp($head, "\x00\x00\x01\x00", 4) === 0) {
        $kind = 'ico';
    } elseif ($ext === 'svg') {
        $svg = (string) file_get_contents($tmpPath);
        if (!preg_match('~<svg[\s>]~i', $svg) || preg_match('~<script|\son[a-z]+\s*=|javascript:|<foreignObject~i', $svg)) {
            throw new RuntimeException('فایل SVG نامعتبر است یا شامل کد اجرایی است.');
        }
        $kind = 'svg';
    } else {
        $info = @getimagesize($tmpPath);
        if (!$info || !in_array($info['mime'] ?? '', ['image/png', 'image/jpeg', 'image/gif', 'image/webp'], true)) {
            throw new RuntimeException('فقط PNG، JPG، WEBP، GIF، ICO یا SVG مجاز است.');
        }
        $kind = $info['mime'] === 'image/png' ? 'png' : 'raster';
    }

    $dir = ROOT_PATH . '/' . THEME_FAVICON_DIR;
    if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
        throw new RuntimeException('پوشه uploads قابل نوشتن نیست.');
    }

    $mode = 'single';
    $img = in_array($kind, ['png', 'raster'], true) ? theme_gd_load($tmpPath) : null;
    if ($img !== null) {
        if (imagesx($img) < 32 || imagesy($img) < 32) {
            throw new RuntimeException('تصویر فاوآیکون باید حداقل ۳۲×۳۲ پیکسل باشد (پیشنهاد: ۵۱۲×۵۱۲).');
        }
        $png = [];
        foreach ([16, 32, 48, 180, 192, 512] as $px) {
            $png[$px] = theme_png_square($img, $px);
        }
        if (PHP_VERSION_ID < 80000) {
            imagedestroy($img);
        }
        theme_clear_favicon_files();
        $files = [
            'favicon-16x16.png' => $png[16], 'favicon-32x32.png' => $png[32], 'favicon-48x48.png' => $png[48],
            'apple-touch-icon-180x180.png' => $png[180], 'android-chrome-192x192.png' => $png[192],
            'android-chrome-512x512.png' => $png[512],
            'favicon.ico' => theme_ico([16 => $png[16], 32 => $png[32], 48 => $png[48]]),
        ];
        foreach ($files as $name => $bytes) {
            if (file_put_contents($dir . '/' . $name, $bytes) === false) {
                throw new RuntimeException('ذخیره فاوآیکون روی هاست ناموفق بود.');
            }
        }
        $mode = 'raster';
    } else {
        // No GD (or an ico/svg): keep the file untouched and point the <link> tags at it.
        if ($kind === 'raster') {
            throw new RuntimeException('برای این فرمت، افزونهٔ GD روی هاست لازم است. تصویر PNG یا SVG یا ICO بدهید.');
        }
        theme_clear_favicon_files();
        if (!@copy($tmpPath, $dir . '/favicon-original.' . $kind)) {
            throw new RuntimeException('ذخیره فاوآیکون روی هاست ناموفق بود.');
        }
    }
    save_setting('theme_favicon_mode', $mode);
    save_setting('theme_favicon_v', (string) time());
    return $mode;
}

function theme_reset_favicon(): void
{
    theme_clear_favicon_files();
    save_setting('theme_favicon_mode', '');
    save_setting('theme_favicon_v', (string) time());
}
