<?php
/**
 * Form field rendering and reading for the admin forms (see resources.php for field types).
 */

declare(strict_types=1);

function field_default(array $def)
{
    $d = $def['default'] ?? '';
    return $d === 'jalali_today' ? jalali_date() : $d;
}

function field_options(array $def): array
{
    if (isset($def['options_from'])) {
        [$table, $key, $label] = $def['options_from'];
        $opts = [];
        foreach (rows("SELECT {$key} AS k, {$label} AS l FROM {$table} ORDER BY sort, id") as $r) {
            $opts[$r['k']] = $r['l'];
        }
        return $opts;
    }
    return $def['options'] ?? [];
}

/** Renders one field wrapped in a Bootstrap column. */
function render_field(string $name, array $def, $value): string
{
    if ($def['type'] === 'schedule') {
        return render_schedule($name, $def, $value);
    }
    $id = 'f_' . $name;
    $label = '<label class="admin-label" for="' . $id . '">' . e($def['label'])
        . (!empty($def['required']) ? ' <span class="admin-required">*</span>' : '') . '</label>';
    $help = isset($def['help']) ? '<div class="admin-help">' . $def['help'] . '</div>' : '';
    $dir = isset($def['dir']) ? ' dir="' . e($def['dir']) . '"' : '';
    $req = !empty($def['required']) ? ' required' : '';
    $val = is_array($value) ? '' : e((string) $value);

    switch ($def['type']) {
        case 'textarea':
            $control = '<textarea class="textarea form-control" id="' . $id . '" name="' . $name . '" rows="' . ($def['rows'] ?? 3) . '"' . $dir . $req . '>' . $val . '</textarea>';
            break;

        case 'html':
            $control = '<div class="admin-editor" data-editor>'
                . '<div class="admin-editor__toolbar">'
                . '<button type="button" data-cmd="p" title="پاراگراف">P</button>'
                . '<button type="button" data-cmd="h2" title="تیتر ۲">H2</button>'
                . '<button type="button" data-cmd="h3" title="تیتر ۳">H3</button>'
                . '<button type="button" data-cmd="strong" title="پررنگ"><i class="feathericon-bold"></i></button>'
                . '<button type="button" data-cmd="em" title="مورب"><i class="feathericon-italic"></i></button>'
                . '<button type="button" data-cmd="link" title="لینک"><i class="feathericon-link"></i></button>'
                . '<button type="button" data-cmd="ul" title="لیست"><i class="feathericon-list"></i></button>'
                . '<button type="button" data-cmd="quote" title="نقل قول">❝</button>'
                . '<button type="button" data-cmd="image" title="درج تصویر"><i class="feathericon-image"></i></button>'
                . '<button type="button" data-cmd="br" title="خط جدید">↵</button>'
                . '<button type="button" data-cmd="preview" class="admin-editor__preview-btn" title="پیش‌نمایش"><i class="feathericon-eye"></i> پیش‌نمایش</button>'
                . '</div>'
                . '<textarea class="textarea form-control admin-editor__code" dir="auto" id="' . $id . '" name="' . $name . '" rows="' . ($def['rows'] ?? 10) . '">' . $val . '</textarea>'
                . '<div class="admin-editor__preview caption-post" hidden></div>'
                . '</div>';
            break;

        case 'image':
            $preview = $value !== '' ? '<img src="' . asset((string) $value, '../') . '" alt="">' : '<i class="feathericon-image"></i>';
            $control = '<div class="admin-image" data-image-field>'
                . '<div class="admin-image__preview">' . $preview . '</div>'
                . '<div class="admin-image__inputs">'
                . '<input type="text" class="input form-control" dir="ltr" id="' . $id . '" name="' . $name . '" value="' . $val . '" placeholder="assets/images/... یا uploads/...">'
                . '<label class="btn btn--small admin-image__upload"><i class="feathericon-upload"></i> <span>آپلود تصویر جدید</span>'
                . '<input type="file" name="__file_' . $name . '" accept="image/*" hidden></label>'
                . '</div></div>';
            break;

        case 'number':
            $control = '<input type="number" step="any" class="input form-control" dir="ltr" id="' . $id . '" name="' . $name . '" value="' . $val . '"' . $req . '>';
            break;

        case 'percent':
            $control = '<div class="admin-range"><input type="range" min="0" max="100" class="form-range" id="' . $id . '" name="' . $name . '" value="' . (int) $value . '" oninput="this.nextElementSibling.textContent=this.value+\'%\'"><span>' . (int) $value . '%</span></div>';
            break;

        case 'bool':
            return '<div class="col-12"><div class="form-group"><div class="form-check form-switch admin-switch">'
                . '<input type="hidden" name="' . $name . '" value="0">'
                . '<input class="form-check-input" type="checkbox" role="switch" id="' . $id . '" name="' . $name . '" value="1"' . ($value ? ' checked' : '') . '>'
                . '<label class="form-check-label" for="' . $id . '">' . e($def['label']) . '</label>'
                . '</div></div></div>';

        case 'select':
            $control = '<select class="custom-select" id="' . $id . '" name="' . $name . '">';
            foreach (field_options($def) as $k => $l) {
                $control .= '<option value="' . e((string) $k) . '"' . ((string) $k === (string) $value ? ' selected' : '') . '>' . e($l) . '</option>';
            }
            $control .= '</select>';
            break;

        case 'slug':
            $control = '<input type="text" class="input form-control" dir="ltr" id="' . $id . '" name="' . $name . '" value="' . $val . '" data-slug-from="f_' . e($def['from'] ?? '') . '" pattern="[A-Za-z0-9_\-]+">';
            break;

        case 'categories':
            $selected = (array) $value;
            $control = '<div class="admin-checks">';
            foreach (rows('SELECT id, name FROM categories ORDER BY sort, id') as $c) {
                $cid = $id . '_' . $c['id'];
                $control .= '<div class="form-check"><input class="form-check-input" type="checkbox" id="' . $cid . '" name="categories[]" value="' . (int) $c['id'] . '"'
                    . (in_array((int) $c['id'], $selected, true) ? ' checked' : '') . '><label class="form-check-label" for="' . $cid . '">' . e($c['name']) . '</label></div>';
            }
            $control .= '</div>';
            break;

        default:
            $control = '<input type="text" class="input form-control" id="' . $id . '" name="' . $name . '" value="' . $val . '"' . $dir . $req . '>';
    }

    $col = $def['col'] ?? 12;
    return '<div class="col-12 col-md-' . $col . '"><div class="form-group">' . $label . $control . $help . '</div></div>';
}

/**
 * Publish control: draft, publish now, or scheduled. Scheduled shows wheel pickers for the time (see admin/assets/publish.js).
 * $value is ['published' => bool, 'at' => ?string]: the stored state of the item, from edit.php.
 */
function render_schedule(string $name, array $def, array $value): string
{
    $stored = isset($value['at']) && $value['at'] !== '' ? (string) $value['at'] : null;
    $state = schedule_form_state($name, (bool) ($value['published'] ?? true), $stored);

    $nowYear = schedule_parts(time())[0];
    $years = array_values(array_unique(array_merge(range($nowYear, $nowYear + 3), [$state['year']])));
    sort($years);

    $maxDay = schedule_days_in_month($state['year'], $state['month']);
    $day = min($state['day'], $maxDay);

    // Each wheel item is [value, label, extra attributes]. Days past the end of the month are hidden, not removed,
    // so the wheel keeps one position per day.
    $days = [];
    for ($d = 1; $d <= 31; $d++) {
        $days[] = [$d, fa_digits((string) $d), $d > $maxDay ? ' hidden' : ''];
    }
    $months = [];
    foreach (jalali_month_names() as $i => $monthName) {
        $months[] = [$i + 1, $monthName, ''];
    }
    $yearItems = [];
    foreach ($years as $y) {
        $yearItems[] = [$y, fa_digits((string) $y), ' data-esfand="' . schedule_days_in_month($y, 12) . '"'];
    }
    $hours = [];
    for ($h = 0; $h < 24; $h++) {
        $hours[] = [$h, fa_digits(sprintf('%02d', $h)), ''];
    }
    $minutes = [];
    for ($m = 0; $m < 60; $m++) {
        $minutes[] = [$m, fa_digits(sprintf('%02d', $m)), ''];
    }

    $wheel = static function (string $key, string $label, array $items, int $selected) use ($name): string {
        $html = '<div class="admin-wheel" data-wheel="' . $key . '">'
            . '<span class="admin-wheel__label">' . e($label) . '</span>'
            . '<div class="admin-wheel__window"><div class="admin-wheel__band" aria-hidden="true"></div>'
            . '<div class="admin-wheel__list" tabindex="0" role="listbox" aria-label="' . e($label) . '">';
        foreach ($items as [$itemValue, $text, $extra]) {
            $on = (int) $itemValue === $selected;
            $html .= '<div class="admin-wheel__item' . ($on ? ' is-selected' : '') . '" role="option"'
                . ' aria-selected="' . ($on ? 'true' : 'false') . '" data-value="' . (int) $itemValue . '"' . $extra . '>'
                . e((string) $text) . '</div>';
        }
        return $html . '</div></div><input type="hidden" name="' . $name . '_' . $key . '" value="' . $selected . '"></div>';
    };

    $wheels = $wheel('day', 'روز', $days, $day)
        . $wheel('month', 'ماه', $months, $state['month'])
        . $wheel('year', 'سال', $yearItems, $state['year'])
        . $wheel('hour', 'ساعت', $hours, $state['hour'])
        . $wheel('minute', 'دقیقه', $minutes, $state['minute']);

    $choices = [
        'draft' => ['پیش‌نویس', 'فقط در پنل مدیریت دیده می‌شود', 'feathericon-edit-3'],
        'now' => ['منتشر شود', 'همین حالا در سایت نمایش داده می‌شود', 'feathericon-globe'],
        'scheduled' => ['انتشار زمان‌بندی شده', 'در زمان انتخابی خودکار منتشر می‌شود', 'feathericon-clock'],
    ];
    $radios = '';
    foreach ($choices as $mode => [$title, $desc, $icon]) {
        $id = $name . '_mode_' . $mode;
        $radios .= '<input type="radio" class="admin-publish__input" id="' . $id . '" name="' . $name . '_mode" value="' . $mode . '"'
            . ($state['mode'] === $mode ? ' checked' : '') . '>'
            . '<label class="admin-choice" for="' . $id . '"><i class="' . $icon . ' admin-choice__icon" aria-hidden="true"></i>'
            . '<span class="admin-choice__title">' . e($title) . '</span>'
            . '<span class="admin-choice__desc">' . e($desc) . '</span></label>';
    }

    $preview = schedule_state_text($state['year'], $state['month'], $day, $state['hour'], $state['minute']);
    $help = isset($def['help']) ? '<div class="admin-help">' . e($def['help']) . '</div>' : '';

    return '<div class="col-12"><div class="form-group">'
        . '<div class="admin-label">' . e($def['label']) . '</div>'
        . '<div class="admin-publish" data-publish role="radiogroup" aria-label="' . e($def['label']) . '">' . $radios
        . '<div class="admin-wheels">'
        . '<div class="admin-wheels__head"><span class="admin-wheels__title">زمان انتشار</span>'
        . '<span class="admin-wheels__preview" data-preview>' . e($preview) . '</span></div>'
        . '<div class="admin-wheels__grid">' . $wheels . '</div>'
        . '</div></div>'
        . $help
        . '</div></div>';
}

/**
 * Reads a posted value for a field. Uploaded files replace the typed path.
 *
 * @throws RuntimeException with a user-facing message on invalid input
 */
function read_field(string $name, array $def)
{
    $raw = $_POST[$name] ?? '';
    $raw = is_string($raw) ? trim($raw) : '';

    switch ($def['type']) {
        case 'bool':
            return $raw === '1' ? 1 : 0;
        case 'percent':
            return max(0, min(100, (int) $raw));
        case 'number':
            return $raw === '' ? '' : (string) (0 + $raw);
        case 'image':
            $uploaded = store_upload($_FILES['__file_' . $name] ?? null);
            return $uploaded ?? $raw;
        case 'select':
            $opts = field_options($def);
            if (!array_key_exists($raw, $opts)) {
                throw new RuntimeException('مقدار «' . $def['label'] . '» معتبر نیست.');
            }
            return $raw;
        case 'schedule':
            return null; // written by schedule_read_choice() in edit.php
        case 'slug':
            $slug = strtolower(preg_replace('~[^A-Za-z0-9_\-]+~', '-', $raw));
            return trim($slug, '-');
        default:
            if (!empty($def['required']) && $raw === '') {
                throw new RuntimeException('فیلد «' . $def['label'] . '» الزامی است.');
            }
            return $raw;
    }
}
