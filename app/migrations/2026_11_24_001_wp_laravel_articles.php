<?php
/**
 * Three articles: WordPress web design, Laravel web design, and when to choose which.
 * Covers carry no text (see assets/images/news/*-cover.jpg); inline figures live in the article bodies.
 * Added once per slug, so later edits in the admin panel are never overwritten.
 */

declare(strict_types=1);

return static function (PDO $pdo): void {
    $articles = [
        [
            'slug'       => 'wordpress-website-design',
            'file'       => 'wordpress-website-design',
            'title'      => 'طراحی سایت وردپرسی؛ راهنمای کامل از انتخاب قالب تا سئو',
            'meta_title' => 'طراحی سایت وردپرسی؛ مراحل، قالب، افزونه و سئو',
            'category'   => 'وردپرس',
            'excerpt'    => 'راهنمای طراحی سایت وردپرسی: مراحل کار، انتخاب قالب آماده یا اختصاصی، افزونه‌های درست، بهینه‌سازی سرعت و امنیت و اصول سئو برای سایت فارسی.',
        ],
        [
            'slug'       => 'laravel-website-design',
            'file'       => 'laravel-website-design',
            'title'      => 'طراحی سایت با لاراول؛ مزیت‌ها، مراحل و نکته‌های امنیتی',
            'meta_title' => 'طراحی سایت با لاراول؛ مزیت‌ها، مراحل و امنیت',
            'category'   => 'لاراول',
            'excerpt'    => 'طراحی سایت با لاراول را بشناسید: مزیت‌های فریم‌ورک، مراحل ساخت پروژه، پنل مدیریت اختصاصی، ساخت API امن و تفاوت آن با وردپرس.',
        ],
        [
            'slug'       => 'wordpress-vs-laravel-which-to-choose',
            'file'       => 'wordpress-vs-laravel',
            'title'      => 'وردپرس یا لاراول؟ برای چه پروژه‌هایی کدام را انتخاب کنیم',
            'meta_title' => 'وردپرس یا لاراول؟ انتخاب درست برای هر پروژه',
            'category'   => 'مقایسه',
            'excerpt'    => 'وردپرس برای چه پروژه‌هایی مناسب است و لاراول برای چه پروژه‌هایی؟ مقایسهٔ هزینه، سرعت، امنیت، سئو و رشد پروژه با چهار پرسش برای تصمیم‌گیری.',
        ],
    ];

    $exists = $pdo->prepare('SELECT COUNT(*) FROM posts WHERE slug = ?');
    $insert = $pdo->prepare(
        'INSERT INTO posts (slug, title, category, date_text, cover, excerpt, intro_html, header_image, body_html, meta_title, meta_desc, is_published, sort)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 0)'
    );
    $dir = __DIR__ . '/content/';

    // Oldest first so the newest id (listed first) is the comparison article.
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
            '۳ آذر ۱۴۰۵',
            'assets/images/news/' . $a['file'] . '-cover.jpg',
            $a['excerpt'],
            (string) file_get_contents($dir . $a['file'] . '.intro.html'),
            'assets/images/news/' . $a['file'] . '-header.jpg',
            (string) file_get_contents($dir . $a['file'] . '.body.html'),
            $a['meta_title'],
            $a['excerpt'],
        ]);
    }
};
