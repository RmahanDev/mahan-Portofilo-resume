<?php
/**
 * Article: WordPress design learning roadmap (seven stages from the basics to a 0-to-100 internship and the job market).
 * Added once per slug, so later edits in the admin panel are never overwritten.
 */

declare(strict_types=1);

return static function (PDO $pdo): void {
    $slug = 'wordpress-learning-roadmap';
    $exists = $pdo->prepare('SELECT COUNT(*) FROM posts WHERE slug = ?');
    $exists->execute([$slug]);
    $found = (int) $exists->fetchColumn();
    $exists->closeCursor();
    if ($found > 0) {
        return;
    }

    $dir = __DIR__ . '/content/';
    $insert = $pdo->prepare(
        'INSERT INTO posts (slug, title, category, date_text, cover, excerpt, intro_html, header_image, body_html, meta_title, meta_desc, is_published, sort)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 0)'
    );
    $insert->execute([
        $slug,
        'نقشهٔ راه یادگیری طراحی سایت وردپرسی؛ از تئوری تا کارآموزی واقعی',
        'وردپرس',
        '۶ آذر ۱۴۰۵',
        'assets/images/news/wordpress-learning-roadmap-cover.jpg',
        'مسیر گام‌به‌گام یادگیری طراحی سایت وردپرسی: از پایه‌های وب و ساخت قالب تا کارآموزی در دو یا چند پروژهٔ واقعی از ۰ تا ۱۰۰ و ورود به بازار کار.',
        (string) file_get_contents($dir . 'wordpress-learning-roadmap.intro.html'),
        'assets/images/news/wordpress-learning-roadmap-header.jpg',
        (string) file_get_contents($dir . 'wordpress-learning-roadmap.body.html'),
        'نقشهٔ راه یادگیری طراحی سایت وردپرسی؛ از تئوری تا کارآموزی',
        'نقشهٔ راه یادگیری طراحی سایت وردپرسی در هفت مرحله: پایه‌های وب، ادیتور بلوکی، ساخت قالب، ابزارهای حرفه‌ای، کارآموزی در چند پروژهٔ واقعی و ورود به بازار کار.',
    ]);
};
