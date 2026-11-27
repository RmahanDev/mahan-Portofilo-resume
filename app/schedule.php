<?php
/**
 * Scheduled publishing for posts and projects.
 *
 * An item with a publish time (publish_at) is a draft until that time. publish_due_items() runs on every request,
 * so the first visit after the time publishes it; shared hosting needs no cron job for this.
 * All times are Tehran wall-clock time, the zone set in bootstrap.php.
 */

declare(strict_types=1);

/** Tables that have a publish_at column (added by migration 2026_11_27_004_scheduled_publish). */
const SCHEDULED_TABLES = ['posts', 'projects'];

/** Publishes every scheduled item whose time has come. Never throws: a public page must not fail because of this. */
function publish_due_items(): void
{
    try {
        $now = date('Y-m-d H:i:s');
        foreach (SCHEDULED_TABLES as $table) {
            // Reading first keeps the common request (nothing due) free of writes.
            $due = row("SELECT id FROM {$table} WHERE publish_at IS NOT NULL AND publish_at <= ? LIMIT 1", [$now]);
            if ($due !== null) {
                q("UPDATE {$table} SET is_published = 1, publish_at = NULL WHERE publish_at IS NOT NULL AND publish_at <= ?", [$now]);
            }
        }
    } catch (Throwable $e) {
        // No publish_at column yet (the migration has not run) or no database: nothing can be due.
    }
}

/** [Jalali year, month, day, hour, minute] of a Tehran timestamp. */
function schedule_parts(int $timestamp): array
{
    [$jy, $jm, $jd] = gregorian_to_jalali((int) date('Y', $timestamp), (int) date('n', $timestamp), (int) date('j', $timestamp));
    return [$jy, $jm, $jd, (int) date('G', $timestamp), (int) date('i', $timestamp)];
}

/** Jalali day with Persian digits, e.g. "۱۰ آذر ۱۴۰۵" (the style of the published articles). */
function schedule_day_text(int $timestamp): string
{
    [$jy, $jm, $jd] = schedule_parts($timestamp);
    return fa_digits(sprintf('%d %s %d', $jd, jalali_month_names()[$jm - 1], $jy));
}

/** Jalali day and clock time of a stored Tehran datetime, e.g. "۱۰ آذر ۱۴۰۵ ساعت ۰۹:۰۰". */
function schedule_label(string $mysql): string
{
    $timestamp = (int) strtotime($mysql);
    return schedule_day_text($timestamp) . ' ساعت ' . fa_digits(date('H:i', $timestamp));
}

/** A number posted by the form, or -1 when it is missing or not a plain number. */
function schedule_posted_number(string $key): int
{
    $value = $_POST[$key] ?? null;
    return is_string($value) && preg_match('~^\d{1,4}$~', $value) === 1 ? (int) $value : -1;
}

/**
 * The publish time posted by the form, as a MySQL datetime in Tehran time.
 *
 * @return string|null null when the schedule switch is off
 * @throws RuntimeException with a message for the admin when the date is not a real moment in the future
 */
function schedule_read_post(string $name): ?string
{
    if (empty($_POST[$name . '_on'])) {
        return null;
    }
    $jy = schedule_posted_number($name . '_year');
    $jm = schedule_posted_number($name . '_month');
    $jd = schedule_posted_number($name . '_day');
    $hour = schedule_posted_number($name . '_hour');
    $minute = schedule_posted_number($name . '_minute');
    if ($jy < 1380 || $jy > 1500 || $jm < 1 || $jm > 12 || $jd < 1 || $jd > 31 || $hour < 0 || $hour > 23 || $minute < 0 || $minute > 59) {
        throw new RuntimeException('زمان انتشار معتبر نیست.');
    }

    [$gy, $gm, $gd] = jalali_to_gregorian($jy, $jm, $jd);
    // A day that the month does not have (31 Mehr, 30 Esfand of a common year) rolls over into the next month.
    if (gregorian_to_jalali($gy, $gm, $gd) !== [$jy, $jm, $jd]) {
        throw new RuntimeException('زمان انتشار معتبر نیست؛ این روز در آن ماه وجود ندارد.');
    }

    $mysql = sprintf('%04d-%02d-%02d %02d:%02d:00', $gy, $gm, $gd, $hour, $minute);
    if ($mysql <= date('Y-m-d H:i:s')) {
        throw new RuntimeException('زمان انتشار گذشته است. اگر این مورد همین حالا منتشر شده، صفحه را دوباره باز کنید. برای انتشار فوری، زمان‌بندی را خاموش کنید و «منتشر شود» را فعال کنید.');
    }
    return $mysql;
}

/**
 * A schedule is for items that are not live yet. Giving a live item a future time would take it off the site
 * (and out of the sitemap and feed) until then, so it is refused; the admin saves it as a draft first.
 *
 * @param array|null $item the stored row, null for a new item
 * @throws RuntimeException
 */
function schedule_check_allowed(?array $item): void
{
    if ($item !== null && !empty($item['is_published']) && empty($item['publish_at'])) {
        throw new RuntimeException('این مورد هم‌اکنون منتشر است. زمان‌بندی فقط برای پیش‌نویس‌ها است؛ اگر می‌خواهید دوباره زمان‌بندی کنید، ابتدا «منتشر شود» را خاموش و ذخیره کنید. صفحه را هم دوباره باز کنید تا وضعیت فعلی را ببینید.');
    }
}

/**
 * The schedule shown in the form: the posted values after a rejected submit, otherwise the stored time,
 * or tomorrow at 09:00 for an item that has none.
 *
 * @return array{on: bool, year: int, month: int, day: int, hour: int, minute: int}
 */
function schedule_form_state(string $name, ?string $stored): array
{
    $keys = ['year', 'month', 'day', 'hour', 'minute'];
    $default = schedule_parts((int) strtotime('tomorrow 09:00'));

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        $state = ['on' => !empty($_POST[$name . '_on'])];
        foreach ($keys as $i => $key) {
            $posted = schedule_posted_number($name . '_' . $key);
            $state[$key] = $posted >= 0 ? $posted : $default[$i];
        }
        return $state;
    }
    if ($stored === null) {
        return ['on' => false] + array_combine($keys, $default);
    }
    return ['on' => true] + array_combine($keys, schedule_parts((int) strtotime($stored)));
}

/**
 * The date shown on the item follows its schedule. A blank date, or one of the dates the form fills in by itself
 * (today, the day the item was created, the day of an earlier schedule), becomes the scheduled day.
 * A date typed by hand is kept.
 *
 * @param array $stored the stored row, or [] for a new item
 */
function schedule_date_text(string $typed, array $stored, string $publishAt): string
{
    $automatic = [date('Y-m-d')];
    foreach (['created_at', 'publish_at'] as $column) {
        if (!empty($stored[$column])) {
            $automatic[] = substr((string) $stored[$column], 0, 10);
        }
    }
    $typedDay = jalali_text_to_iso($typed);
    if (trim($typed) === '' || ($typedDay !== null && in_array($typedDay, $automatic, true))) {
        return schedule_day_text((int) strtotime($publishAt));
    }
    return $typed;
}
