<?php
/**
 * Admin: theme and colors (every color of every section), fonts (upload) and favicon (upload).
 * The logic lives in app/theme.php; this page only collects input and renders the forms.
 */
require dirname(__DIR__) . '/app/admin/core.php';
require_admin();

/** The uploaded file of an <input type=file>, null when nothing was chosen; throws on a failed upload. */
function theme_posted_file(string $field): ?array
{
    $f = $_FILES[$field] ?? null;
    if (!$f || ($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($f['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('آپلود فایل ناموفق بود (کد خطا ' . $f['error'] . '). حجم فایل از حد مجاز هاست بیشتر است؟');
    }
    if (!is_uploaded_file($f['tmp_name'])) {
        throw new RuntimeException('فایل آپلود شده معتبر نیست.');
    }
    return $f;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // A file larger than post_max_size makes PHP drop the whole request body.
    if (!$_POST && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        flash('error', 'حجم فایل از حد مجاز سرور بیشتر است.');
        redirect('theme.php');
    }
    verify_post();
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'preview') {
        header('Content-Type: text/css; charset=utf-8');
        echo theme_build_css(theme_overrides_from_post($_POST), '.theme-preview', '.theme-preview', true);
        exit;
    }

    $anchor = 'colors';
    try {
        switch ($action) {
            case 'save_colors':
                theme_save_colors($_POST);
                save_setting('theme_apply_admin', !empty($_POST['theme_apply_admin']) ? '1' : '0');
                flash('success', 'رنگ‌ها ذخیره شد و روی سایت اعمال شد.');
                break;

            case 'reset_colors':
                theme_reset_colors();
                flash('success', 'همهٔ رنگ‌ها به حالت پیش‌فرض برگشت.');
                break;

            case 'save_fonts':
                $anchor = 'fonts';
                $changed = 0;
                foreach (['body', 'heading'] as $slot) {
                    if (!empty($_POST['reset_' . $slot])) {
                        theme_reset_font($slot);
                        $changed++;
                    } elseif ($f = theme_posted_file('font_' . $slot)) {
                        theme_install_font($f['tmp_name'], (string) $f['name'], $slot);
                        $changed++;
                    }
                }
                flash($changed ? 'success' : 'error', $changed ? 'فونت‌ها به‌روزرسانی شد.' : 'فایل فونتی انتخاب نشده است.');
                break;

            case 'save_favicon':
                $anchor = 'favicon';
                $f = theme_posted_file('favicon');
                if (!$f) {
                    throw new RuntimeException('فایلی برای فاوآیکون انتخاب نشده است.');
                }
                $mode = theme_install_favicon($f['tmp_name'], (string) $f['name']);
                flash('success', $mode === 'raster'
                    ? 'فاوآیکون ذخیره شد و همهٔ اندازه‌ها (۱۶، ۳۲، ۴۸، ۱۸۰) ساخته شد.'
                    : 'فاوآیکون ذخیره شد.');
                break;

            case 'reset_favicon':
                $anchor = 'favicon';
                theme_reset_favicon();
                flash('success', 'فاوآیکون به حالت پیش‌فرض برگشت.');
                break;

            default:
                throw new RuntimeException('درخواست نامعتبر است.');
        }
    } catch (RuntimeException $ex) {
        flash('error', $ex->getMessage());
    }
    redirect('theme.php#' . $anchor);
}

$pageTitle = 'تم و رنگ‌ها';
$menu = 'theme';
$overrides = theme_overrides();
$effective = theme_effective($overrides);
$defaults = theme_defaults();
$sections = theme_sections();
$extraHead = '    <style>' . theme_font_css('../', true) . '</style>' . "\n";

/** One color field: swatch + hex text + reset button. $inherit = global key a section color falls back to. */
function theme_field_html(string $name, string $id, string $label, string $value, string $fallback, string $help = '', string $inherit = ''): string
{
    $shown = $value !== '' ? $value : $fallback;
    return '<div class="col-12 col-md-6"><div class="form-group theme-color" data-default="' . e($fallback) . '"'
        . ($inherit !== '' ? ' data-inherit="' . e($inherit) . '"' : '') . '>'
        . '<label class="admin-label" for="' . e($id) . '">' . e($label) . '</label>'
        . '<div class="theme-color__ctl">'
        . '<input type="color" class="theme-color__swatch" value="' . e($shown) . '" aria-label="انتخاب رنگ ' . e($label) . '">'
        . '<input type="text" class="input form-control" dir="ltr" id="' . e($id) . '" name="' . e($name) . '" value="' . e($value) . '" placeholder="' . e($fallback) . '" maxlength="7" autocomplete="off" spellcheck="false">'
        . '<button type="button" class="admin-icon-btn" data-theme-reset title="بازنشانی" aria-label="بازنشانی ' . e($label) . '"><i class="feathericon-rotate-ccw"></i></button>'
        . '</div>'
        . ($help !== '' ? '<div class="admin-help">' . e($help) . '</div>' : '')
        . '</div></div>';
}

function theme_font_info(string $slot): array
{
    $path = theme_font_path($slot);
    return $path === '' ? [] : ['path' => $path, 'name' => basename($path), 'size' => (int) filesize(ROOT_PATH . '/' . $path)];
}

$favMode = setting('theme_favicon_mode');
$favSrc = $favMode === 'raster' ? '../uploads/favicon/apple-touch-icon-180x180.png' : '../assets/images/favicons/apple-touch-icon-180x180.png';
if ($favMode === 'single') {
    foreach (['svg', 'ico', 'png'] as $ext) {
        if (is_file(ROOT_PATH . '/uploads/favicon/favicon-original.' . $ext)) {
            $favSrc = '../uploads/favicon/favicon-original.' . $ext;
        }
    }
}
$favSrc .= '?v=' . setting('theme_favicon_v', '1');

require dirname(__DIR__) . '/app/admin/layout_top.php';
?>
                        <p class="admin-help theme-intro">رنگ‌ها، فونت و آیکون تب مرورگر را از همین‌جا برای کل سایت تعیین کنید. هر فیلد خالی یعنی «همان رنگ پیش‌فرض / رنگ کلی».</p>

                        <div class="theme-tabs" role="tablist">
                            <a href="#colors" class="btn btn--small"><i class="feathericon-droplet"></i> <span>رنگ‌ها</span></a>
                            <a href="#fonts" class="btn btn--small"><i class="feathericon-type"></i> <span>فونت</span></a>
                            <a href="#favicon" class="btn btn--small"><i class="feathericon-image"></i> <span>فاوآیکون</span></a>
                        </div>

                        <!-- ===================== colors ===================== -->
                        <section id="colors" class="theme-panel">
                            <h2 class="title title--h2">رنگ‌ها</h2>

                            <div class="theme-preview" id="theme-preview" aria-label="پیش‌نمایش زنده">
                                <div class="theme-preview__tag">پیش‌نمایش زنده</div>
                                <div class="row">
                                    <div class="col-12 col-md-4">
                                        <div class="sidebar box-outer">
                                            <div class="sidebar__base-info">
                                                <figure class="avatar-box"><img src="<?= asset(setting('profile_avatar'), '../') ?>" alt=""></figure>
                                                <div class="text-center">
                                                    <h3 class="title title--h3 sidebar__name"><?= s('profile_name') ?></h3>
                                                    <div class="badge"><?= s('profile_badge') ?></div>
                                                </div>
                                            </div>
                                            <div class="separation"></div>
                                            <ul class="details-info">
                                                <li class="details-info__item">
                                                    <span class="box icon-box"><i class="font-icon icon-envelope"></i></span>
                                                    <div class="contacts-block__info"><span class="overhead">ایمیل</span><a class="text-overflow" href="#" onclick="return false"><?= s('contact_email') ?></a></div>
                                                </li>
                                            </ul>
                                            <div class="social">
                                                <a class="social__link" href="#" onclick="return false"><i class="feathericon-github"></i></a>
                                                <a class="social__link" href="#" onclick="return false"><i class="feathericon-linkedin"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-8">
                                        <div class="box-outer theme-preview__main">
                                            <div class="nav-container">
                                                <ul class="nav">
                                                    <li class="nav__item"><a class="active" href="#" onclick="return false">درباره من</a></li>
                                                    <li class="nav__item"><a href="#" onclick="return false">رزومه</a></li>
                                                    <li class="nav__item"><a href="#" onclick="return false">وبلاگ</a></li>
                                                </ul>
                                            </div>
                                            <h1 class="title title--h1 title__separate">تیتر صفحه</h1>
                                            <p>این یک متن نمونه است. <a href="#" onclick="return false">این یک لینک است</a> و دکمهٔ زیر رنگ اصلی را نشان می‌دهد.</p>
                                            <p><button type="button" class="btn"><i class="font-icon icon-send"></i> ارسال پیام</button></p>
                                            <div class="case-item box box-inner">
                                                <span class="box icon-box case-item__icon"><i class="font-icon icon-envelope"></i></span>
                                                <div>
                                                    <h3 class="title title--h3">کارت خدمات</h3>
                                                    <p class="case-item__caption">توضیح کوتاه یک خدمت در صفحهٔ درباره من.</p>
                                                </div>
                                            </div>
                                            <div class="timeline">
                                                <article class="timeline__item">
                                                    <h5 class="title title--h5 timeline__title">یک سابقه کاری</h5>
                                                    <span class="timeline__period">۱۴۰۲ - اکنون</span>
                                                    <p class="timeline__description">توضیح سابقه در تایم‌لاین رزومه.</p>
                                                </article>
                                            </div>
                                            <div class="progress">
                                                <div class="progress-text"><span>مهارت نمونه</span> 80%</div>
                                                <div class="progress-bar"><span role="progressbar" style="width: 80%"></span></div>
                                            </div>
                                            <div class="info-box"><h4 class="info-box__title">کادر اطلاعات</h4><p class="mb-0">متنی داخل کادر اطلاع‌رسانی مقاله.</p></div>
                                            <div class="callout callout--warn"><span class="callout__title">هشدار</span><p>نمونهٔ کادر هشدار.</p></div>
                                            <div class="contact-form">
                                                <input type="text" class="input form-control" placeholder="فیلد فرم تماس">
                                            </div>
                                            <div class="theme-preview__loading">
                                                <div class="preloader theme-preview__preloader"><div class="preloader__bar"><span style="width:60%"></span></div></div>
                                                <span class="theme-preview__note">نوار لودینگ</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <style id="theme-preview-style"></style>

                            <form method="post" class="admin-form box box-inner" id="theme-colors-form" autocomplete="off">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="save_colors">

                                <h3 class="title title--h3 theme-group-title">رنگ‌های کلی سایت</h3>
                                <p class="admin-help">این رنگ‌ها روی همهٔ صفحه‌ها اثر می‌گذارند. رنگ هر بخش را می‌توانید پایین‌تر جداگانه عوض کنید.</p>
<?php foreach (theme_palette() as $groupLabel => $fields): ?>
                                <h4 class="theme-group-sub"><?= e($groupLabel) ?></h4>
                                <div class="row">
<?php foreach ($fields as $key => [$label, $default, $help]): ?>
                                    <?= theme_field_html('c[' . $key . ']', 'c_' . $key, $label, $overrides['c'][$key] ?? '', $default, $help) ?>
<?php endforeach; ?>
                                </div>
<?php endforeach; ?>

                                <h3 class="title title--h3 theme-group-title">رنگ هر بخش به‌صورت جداگانه</h3>
                                <p class="admin-help">اگر فیلدی خالی بماند، همان رنگ کلی بالا استفاده می‌شود. هر بخش را باز کنید و رنگ‌های همان قسمت را تغییر دهید.</p>
<?php foreach ($sections as $sec => $def): $count = count($overrides['s'][$sec] ?? []); ?>
                                <details class="theme-section"<?= $count ? ' open' : '' ?>>
                                    <summary>
                                        <span><?= e($def['label']) ?></span>
                                        <span class="admin-pill<?= $count ? ' admin-pill--on' : '' ?>" data-section-count="<?= e($sec) ?>"><?= $count ? $count . ' رنگ سفارشی' : 'پیش‌فرض' ?></span>
                                    </summary>
                                    <div class="row">
<?php foreach ($def['keys'] as $key): ?>
                                        <?= theme_field_html('s[' . $sec . '][' . $key . ']', 's_' . $sec . '_' . $key, theme_color_label($key), $overrides['s'][$sec][$key] ?? '', $effective[$key], '', $key) ?>
<?php endforeach; ?>
                                    </div>
                                </details>
<?php endforeach; ?>

                                <div class="form-group mt-4">
                                    <div class="form-check form-switch admin-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="theme_apply_admin" name="theme_apply_admin" value="1"<?= setting('theme_apply_admin') === '1' ? ' checked' : '' ?>>
                                        <label class="form-check-label" for="theme_apply_admin">اعمال رنگ‌ها و فونت روی پنل مدیریت هم</label>
                                    </div>
                                    <div class="admin-help">اگر رنگ‌ها نخوانا شدند، به انتهای آدرس هر صفحهٔ پنل <code dir="ltr">?notheme=1</code> اضافه کنید تا پنل با ظاهر پیش‌فرض باز شود.</div>
                                </div>

                                <div class="admin-actions">
                                    <button type="submit" class="btn"><i class="feathericon-save"></i> ذخیره رنگ‌ها</button>
                                </div>
                            </form>

                            <form method="post" class="theme-reset-form">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="reset_colors">
                                <button type="submit" class="btn btn--small admin-btn-danger" data-confirm="همهٔ رنگ‌های سفارشی پاک شود و سایت به رنگ‌های پیش‌فرض برگردد؟"><i class="feathericon-rotate-ccw"></i> بازنشانی همهٔ رنگ‌ها به پیش‌فرض</button>
                            </form>
                        </section>

                        <!-- ===================== fonts ===================== -->
                        <section id="fonts" class="theme-panel">
                            <h2 class="title title--h2">فونت</h2>
                            <form method="post" enctype="multipart/form-data" class="admin-form box box-inner">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="save_fonts">
                                <p class="admin-help">فایل فونت جدید را آپلود کنید (woff2 بهترین است؛ woff، ttf و otf هم قبول است، حداکثر ۶ مگابایت). فونت جدید روی همهٔ وزن‌ها (نازک تا ضخیم) استفاده می‌شود. برای برگشت، تیک «حذف» را بزنید.</p>
<?php foreach (['body' => ['فونت اصلی سایت', 'متن‌ها، منو، دکمه‌ها، فرم‌ها و تیترهای h3 تا h5 (پیش‌فرض: IRY)', 'font-family:\'PF Body\',\'IRY\',sans-serif'],
                'heading' => ['فونت تیترهای بزرگ', 'تیترهای h1، h2 و h6 (پیش‌فرض: Sarb)', 'font-family:\'PF Heading\',\'Sarb\',\'IRY\',sans-serif']] as $slot => [$flabel, $fhelp, $fstyle]):
    $info = theme_font_info($slot); ?>
                                <div class="theme-font" data-font-slot="<?= e($slot) ?>">
                                    <label class="admin-label" for="font_<?= e($slot) ?>"><?= e($flabel) ?></label>
                                    <div class="admin-help"><?= e($fhelp) ?></div>
                                    <div class="theme-font__row">
                                        <input type="file" class="form-control" id="font_<?= e($slot) ?>" name="font_<?= e($slot) ?>" accept=".woff2,.woff,.ttf,.otf,font/woff2,font/woff,font/ttf,font/otf">
<?php if ($info): ?>
                                        <span class="admin-pill admin-pill--on" dir="ltr"><?= e($info['name']) ?> · <?= round($info['size'] / 1024) ?> KB</span>
                                        <label class="admin-switch-inline"><input type="checkbox" name="reset_<?= e($slot) ?>" value="1"> حذف و برگشت به فونت پیش‌فرض</label>
<?php else: ?>
                                        <span class="admin-pill">فونت پیش‌فرض</span>
<?php endif; ?>
                                    </div>
                                    <p class="theme-font__sample" data-font-sample style="<?= $info ? e($fstyle) : '' ?>">نمونه متن: سلام، به وب‌سایت من خوش آمدید ۰۱۲۳۴۵۶۷۸۹ — The quick brown fox</p>
                                </div>
<?php endforeach; ?>
                                <div class="admin-actions">
                                    <button type="submit" class="btn"><i class="feathericon-upload"></i> ذخیره فونت‌ها</button>
                                </div>
                            </form>
                        </section>

                        <!-- ===================== favicon ===================== -->
                        <section id="favicon" class="theme-panel">
                            <h2 class="title title--h2">فاوآیکون (آیکون تب مرورگر)</h2>
                            <form method="post" enctype="multipart/form-data" class="admin-form box box-inner">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="save_favicon">
                                <div class="theme-favicon">
                                    <div class="theme-favicon__preview"><img src="<?= e($favSrc) ?>" alt="فاوآیکون فعلی" id="favicon-preview"></div>
                                    <div class="theme-favicon__inputs">
                                        <label class="admin-label" for="favicon_file">تصویر جدید</label>
                                        <input type="file" class="form-control" id="favicon_file" name="favicon" accept="image/png,image/jpeg,image/webp,image/gif,image/svg+xml,.ico,.svg">
                                        <div class="admin-help">
                                            بهتر است تصویر مربع PNG با اندازهٔ ۵۱۲×۵۱۲ باشد؛ همهٔ اندازه‌های لازم (۱۶، ۳۲، ۴۸، آیکون اپل ۱۸۰ و فایل ico) خودکار ساخته می‌شود. SVG و ICO هم قبول است.
<?php if (!function_exists('imagecreatefromstring')): ?>
                                            <br><strong>توجه:</strong> افزونهٔ GD روی این هاست فعال نیست؛ فقط PNG، SVG و ICO بدون تغییر اندازه ذخیره می‌شود.
<?php endif; ?>
                                        </div>
                                        <div class="theme-favicon__state"><span class="admin-pill<?= $favMode !== '' ? ' admin-pill--on' : '' ?>"><?= $favMode !== '' ? 'فاوآیکون سفارشی فعال است' : 'فاوآیکون پیش‌فرض' ?></span></div>
                                    </div>
                                </div>
                                <div class="admin-actions">
                                    <button type="submit" class="btn"><i class="feathericon-upload"></i> ذخیره فاوآیکون</button>
                                </div>
                            </form>
<?php if ($favMode !== ''): ?>
                            <form method="post" class="theme-reset-form">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="reset_favicon">
                                <button type="submit" class="btn btn--small admin-btn-danger" data-confirm="فاوآیکون سفارشی حذف شود و آیکون پیش‌فرض برگردد؟"><i class="feathericon-trash-2"></i> حذف فاوآیکون سفارشی</button>
                            </form>
<?php endif; ?>
                            <p class="admin-help">مرورگرها فاوآیکون را کش می‌کنند؛ بعد از تغییر ممکن است چند دقیقه یا تا بستن و بازکردن تب طول بکشد.</p>
                        </section>
<?php require dirname(__DIR__) . '/app/admin/layout_bottom.php';
