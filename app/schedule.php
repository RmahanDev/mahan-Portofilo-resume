<?php
/**
 * Publishing for posts and projects: a draft, published now, or scheduled for a Tehran time.
 *
 * A scheduled item is kept as a draft (is_published = 0) with its time in publish_at. publish_due_items() runs on
 * every request, so the first visit after the time publishes it; shared hosting needs no cron job for this.
 */

declare(strict_types=1);

/** Tables that have a publish_at column (added by migration 2026_11_27_004_scheduled_publish). */
const SCHEDULED_TABLES = ['posts', 'projects'];

/** The choices of the publish control in the admin form. */
const PUBLISH_MODES = ['draft', 'now', 'scheduled'];

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

/** The same time written from its parts, for the preview in the form. */
function schedule_state_text(int $year, int $month, int $day, int $hour, int $minute): string
{
    return fa_digits(sprintf('%d %s %d', $day, jalali_month_names()[$month - 1], $year))
        . ' ساعت ' . fa_digits(sprintf('%02d:%02d', $hour, $minute));
}

/** Number of days in a Jalali month. Esfand has 30 days in a leap year and 29 otherwise. */
function schedule_days_in_month(int $jy, int $jm): int
{
    if ($jm <= 6) {
        return 31;
    }
    if ($jm <= 11) {
        return 30;
    }
    [$gy, $gm, $gd] = jalali_to_gregorian($jy, 12, 30);
    return gregorian_to_jalali($gy, $gm, $gd) === [$jy, 12, 30] ? 30 : 29;
}

/** A number posted by the form, or -1 when it is missing or not a plain number. */
function schedule_posted_number(string $key): int
{
    $value = $_POST[$key] ?? null;
    return is_string($value) && preg_match('~^\d{1,4}$~', $value) === 1 ? (int) $value : -1;
}

/**
 * The publish time posted by the wheel pickers, as a MySQL datetime in Tehran time.
 *
 * @throws RuntimeException with a message for the admin when the date is not a real moment in the future
 */
function schedule_read_time(string $name): string
{
    $jy = schedule_posted_number($name . '_year');
    $jm = schedule_posted_number($name . '_month');
    $jd = schedule_posted_number($name . '_day');
    $hour = schedule_posted_number($name . '_hour');
    $minute = schedule_posted_number($name . '_minute');
    if ($jy < 1380 || $jy > 1500 || $jm < 1 || $jm > 12 || $jd < 1 || $jd > 31 || $hour < 0 || $hour > 23 || $minute < 0 || $minute > 59) {
        throw new RuntimeException('زمان انتشار معتبر نیست.');
    }
    if ($jd > schedule_days_in_month($jy, $jm)) {
        throw new RuntimeException('زمان انتشار معتبر نیست؛ این روز در آن ماه وجود ندارد.');
    }

    [$gy, $gm, $gd] = jalali_to_gregorian($jy, $jm, $jd);
    $mysql = sprintf('%04d-%02d-%02d %02d:%02d:00', $gy, $gm, $gd, $hour, $minute);
    if ($mysql <= date('Y-m-d H:i:s')) {
        throw new RuntimeException('زمان انتشار گذشته است. اگر این مورد در این فاصله منتشر شده، صفحه را دوباره باز کنید.');
    }
    return $mysql;
}

/**
 * A schedule is for items that are not live yet. Giving a live item a future time would take it off the site
 * (and out of the sitemap and feed) until then, so it is refused; the admin chooses draft first.
 *
 * @param array|null $item the stored row, null for a new item
 * @throws RuntimeException
 */
function schedule_check_allowed(?array $item): void
{
    if ($item !== null && !empty($item['is_published']) && empty($item['publish_at'])) {
        throw new RuntimeException('این مورد هم‌اکنون منتشر است. برای زمان‌بندی، ابتدا گزینهٔ «پیش‌نویس» را انتخاب و ذخیره کنید.');
    }
}

/**
 * The publish choice posted by the form: draft, now, or scheduled.
 *
 * @param array|null $item the stored row, null for a new item
 * @return array{0: int, 1: ?string} [is_published, publish_at]
 * @throws RuntimeException
 */
function schedule_read_choice(string $name, ?array $item): array
{
    $mode = $_POST[$name . '_mode'] ?? '';
    if (!is_string($mode) || !in_array($mode, PUBLISH_MODES, true)) {
        throw new RuntimeException('وضعیت انتشار معتبر نیست.');
    }
    if ($mode === 'draft') {
        return [0, null];
    }
    if ($mode === 'now') {
        return [1, null];
    }
    schedule_check_allowed($item);
    return [0, schedule_read_time($name)];
}

/**
 * What the publish control shows: the chosen mode and the wheel values.
 * After a rejected submit it shows what was posted; otherwise the stored item. A new item starts as "now".
 *
 * @return array{mode: string, year: int, month: int, day: int, hour: int, minute: int}
 */
function schedule_form_state(string $name, bool $published, ?string $stored): array
{
    $keys = ['year', 'month', 'day', 'hour', 'minute'];
    $default = schedule_parts((int) strtotime('tomorrow 09:00'));

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        $posted = $_POST[$name . '_mode'] ?? '';
        $state = ['mode' => is_string($posted) && in_array($posted, PUBLISH_MODES, true) ? $posted : 'now'];
        $limits = [[1380, 1500], [1, 12], [1, 31], [0, 23], [0, 59]];
        foreach ($keys as $i => $key) {
            $value = schedule_posted_number($name . '_' . $key);
            $state[$key] = $value >= $limits[$i][0] && $value <= $limits[$i][1] ? $value : $default[$i];
        }
        return $state;
    }

    $mode = $stored !== null ? 'scheduled' : ($published ? 'now' : 'draft');
    $parts = $stored !== null ? schedule_parts((int) strtotime($stored)) : $default;
    return ['mode' => $mode] + array_combine($keys, $parts);
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
