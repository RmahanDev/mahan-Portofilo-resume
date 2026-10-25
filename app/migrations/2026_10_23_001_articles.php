<?php
/**
 * Three real articles replace the lorem-ipsum samples on the blog.
 * Samples are only hidden when they still contain the placeholder text, so edited posts stay as they are.
 */

declare(strict_types=1);

return static function (PDO $pdo): void {
    $pdo->exec("UPDATE posts SET is_published = 0
                WHERE slug IN ('post-1','post-2','post-3','post-4','post-5','post-6')
                  AND (excerpt LIKE '%لورم ایپسوم%' OR body_html LIKE '%لورم ایپسوم%')");

    $articles = [
        [
            'slug'     => 'learn-web-design-roadmap',
            'file'     => 'learn-web-design',
            'title'    => 'اصول یادگیری طراحی سایت؛ نقشهٔ راه از صفر تا نمونه‌کار',
            'category' => 'آموزش',
            'excerpt'  => 'مسیر گام‌به‌گام یادگیری طراحی سایت از صفر: HTML، CSS، JavaScript، اصول طراحی، روش‌های درست یادگیری و پنج پروژهٔ تمرینی.',
        ],
        [
            'slug'     => 'professional-resume-principles',
            'file'     => 'resume-guide',
            'title'    => 'اصول نوشتن رزومهٔ حرفه‌ای برای برنامه‌نویسان و متخصصان IT',
            'category' => 'رزومه',
            'excerpt'  => 'ساختار درست رزومه، نوشتن دستاورد به‌جای وظیفه، ظاهر رزومه، اشتباه‌های رایج و چک‌لیست نهایی پیش از ارسال برای متخصصان نرم‌افزار و IT.',
        ],
        [
            'slug'     => 'job-hunting-guide',
            'file'     => 'job-hunting',
            'title'    => 'نقشهٔ راه پیدا کردن کار در حوزهٔ نرم‌افزار؛ از رزومه تا امضای قرارداد',
            'category' => 'کاریابی',
            'excerpt'  => 'نقشهٔ کامل پیدا کردن کار در حوزهٔ نرم‌افزار و IT: از تعیین هدف و آماده‌سازی رزومه تا مصاحبه، مذاکره و برنامهٔ سی‌روزهٔ کاریابی.',
        ],
    ];

    $exists = $pdo->prepare('SELECT COUNT(*) FROM posts WHERE slug = ?');
    $insert = $pdo->prepare(
        'INSERT INTO posts (slug, title, category, date_text, cover, excerpt, intro_html, header_image, body_html, meta_desc, is_published, sort)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 0)'
    );
    $dir = __DIR__ . '/content/';

    // Inserted oldest-first so the newest id (listed first) is the job-hunting guide.
    foreach ($articles as $a) {
        $exists->execute([$a['slug']]);
        $found = (int) $exists->fetchColumn();
        $exists->closeCursor();
        if ($found > 0) {
            continue;
        }
        $insert->execute([
            $a['slug'],
            $a['title'],
            $a['category'],
            '۳۰ مهر ۱۴۰۵',
            'assets/images/news/' . $a['file'] . '-cover.jpg',
            $a['excerpt'],
            (string) file_get_contents($dir . $a['file'] . '.intro.html'),
            'assets/images/news/' . $a['file'] . '-header.jpg',
            (string) file_get_contents($dir . $a['file'] . '.body.html'),
            $a['excerpt'],
        ]);
    }
};
