<?php
/**
 * SEO pass: descriptive page titles and descriptions, proper project titles, and light JPEG versions of the
 * two heaviest cover images. Settings and titles are only replaced while they still hold the old defaults,
 * so anything edited in the admin is kept.
 */

declare(strict_types=1);

return static function (PDO $pdo): void {
    $site = 'مدیر IT و توسعه‌دهندهٔ وب با تجربه در PHP، لاراول و وردپرس. نمونه‌کارها، افزونه‌های رایگان وردپرس، رزومه و راه‌های تماس برای سفارش پروژه.';
    $new = [
        'site_description'          => ['درباره ماهان رضایی | مدیریت IT | توسعه وب | امنیت شبکه', 'ماهان رضایی، ' . $site],
        'seo_index_title'           => ['درباره ماهان رضایی', 'ماهان رضایی | مدیر IT و توسعه‌دهندهٔ وب (لاراول، PHP، وردپرس)'],
        'seo_index_description'     => ['درباره ماهان رضایی | مدیریت IT | توسعه وب | امنیت شبکه', 'ماهان رضایی، ' . $site],
        'seo_resume_title'          => ['رزومه ماهان رضایی', 'رزومه ماهان رضایی | تجربه کاری، تحصیلات و مهارت‌ها'],
        'seo_resume_description'    => ['رزومه ماهان رضایی | مدیریت IT | توسعه وب | امنیت شبکه', 'رزومهٔ ماهان رضایی: سوابق کاری در مدیریت IT و توسعهٔ وب، تحصیلات و مهارت‌ها در PHP، لاراول، وردپرس، پایتون و امنیت شبکه.'],
        'seo_portfolio_title'       => ['نمونه کار های من', 'نمونه‌کارهای ماهان رضایی | طراحی وب، وردپرس و شبکه'],
        'seo_portfolio_description' => ['', 'نمونه‌کارهای طراحی و توسعهٔ وب، افزونه‌های رایگان وردپرس، داشبورد مدیریت، سامانهٔ چت و زیرساخت شبکه از ماهان رضایی.'],
        'seo_blog_title'            => ['وبلاگ', 'وبلاگ ماهان رضایی | مقالات کاریابی، رزومه و طراحی سایت'],
        'seo_blog_description'      => ['', 'مقالات آموزشی دربارهٔ پیدا کردن کار در حوزهٔ نرم‌افزار، نوشتن رزومهٔ حرفه‌ای و یادگیری طراحی سایت.'],
        'seo_contact_title'         => ['تماس با ماهان رضایی', 'تماس با ماهان رضایی | سفارش پروژه و همکاری'],
        'seo_contact_description'   => ['تماس با ماهان رضایی | مدیریت IT | توسعه وب | امنیت شبکه', 'برای سفارش طراحی سایت، توسعهٔ وب و مشاورهٔ IT با ماهان رضایی تماس بگیرید: فرم تماس، ایمیل و شمارهٔ تلفن.'],
    ];
    $read  = $pdo->prepare('SELECT v FROM settings WHERE k = ?');
    $write = $pdo->prepare('INSERT INTO settings (k, v) VALUES (?, ?) ON DUPLICATE KEY UPDATE v = VALUES(v)');
    foreach ($new as $key => [$old, $value]) {
        $read->execute([$key]);
        $current = $read->fetchColumn();
        if ($current === false || $current === $old || $current === '') {
            $write->execute([$key, $value]);
        }
    }

    // Short placeholders like "پروژه ورسک" become the full project title.
    $pdo->exec("UPDATE projects SET meta_title = title WHERE meta_title LIKE 'پروژه %' AND CHAR_LENGTH(meta_title) < 30");

    // Lighter JPEG versions (generated next to the PNG originals) for the heaviest cover and header images.
    $swap = $pdo->prepare('UPDATE projects SET cover = ? WHERE slug = ? AND cover = ?');
    $swapHeader = $pdo->prepare('UPDATE projects SET header_image = ? WHERE slug = ? AND header_image = ?');
    $p = 'assets/images/projects/';
    $swap->execute([$p . 'telamon/tel4.jpg', 'academia', $p . 'telamon/tel4.png']);
    $swapHeader->execute([$p . 'telamon/tel4.jpg', 'academia', $p . 'telamon/tel4.png']);
    $swap->execute([$p . 'hamyar/hamyar11.jpg', 'amozesh', $p . 'hamyar/hamyar11.png']);
    $swapHeader->execute([$p . 'hamyar/hamyar12.jpg', 'amozesh', $p . 'hamyar/hamyar12.png']);
};
