<?php
/**
 * Two free WordPress plugins as portfolio items, each with a download box.
 * The zip files live in assets/downloads/. Items that already exist (same slug) are left untouched.
 */

declare(strict_types=1);

return static function (PDO $pdo): void {
    $projects = [
        [
            'slug'           => 'moaref-wordpress-plugin',
            'file'           => 'moaref',
            'title'          => 'افزونهٔ وردپرس کد معرف و کیف پول (Moaref)',
            'heading_html'   => 'کد معرف و کیف پول (Moaref)<br/>افزونهٔ رایگان وردپرس',
            'image'          => 'assets/images/projects/moaref-plugin.jpg',
            'header'         => 'assets/images/projects/moaref-plugin-header.jpg',
            'date_text'      => '۲ آبان ۱۴۰۵',
            'meta_desc'      => 'Moaref؛ افزونهٔ رایگان وردپرس و ووکامرس برای کد معرف، کمیسیون، کش‌بک و کیف پول تومانی. دانلود رایگان و ویرایش آزاد کد.',
            'sort'           => 0,
        ],
        [
            'slug'           => 'shainika-sms-auth-wordpress-plugin',
            'file'           => 'sms-auth',
            'title'          => 'افزونهٔ ورود پیامکی وردپرس با ملی‌پیامک (Shainika SMS Auth)',
            'heading_html'   => 'ورود پیامکی با ملی‌پیامک<br/>افزونهٔ رایگان وردپرس',
            'image'          => 'assets/images/projects/sms-auth-plugin.jpg',
            'header'         => 'assets/images/projects/sms-auth-plugin-header.jpg',
            'date_text'      => '۲ آبان ۱۴۰۵',
            'meta_desc'      => 'Shainika SMS Auth؛ افزونهٔ رایگان ورود و ثبت‌نام با کد یک‌بارمصرف پیامکی برای وردپرس و ووکامرس با ملی‌پیامک و چند سرویس دیگر. دانلود رایگان.',
            'sort'           => 0,
        ],
    ];

    $category = (int) $pdo->query("SELECT id FROM categories WHERE slug = 'wordpress'")->fetchColumn();
    $exists   = $pdo->prepare('SELECT COUNT(*) FROM projects WHERE slug = ?');
    $insert   = $pdo->prepare(
        'INSERT INTO projects (slug, title, card_category, cover, heading_html, header_image, client, category_label, date_text, body_html, meta_title, meta_desc, is_published, sort)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)'
    );
    $link = $pdo->prepare('INSERT INTO project_categories (project_id, category_id) VALUES (?, ?)');
    $dir  = __DIR__ . '/content/';

    // Same sort value, so the lower id is listed first: Moaref goes in before the SMS plugin.
    foreach ($projects as $p) {
        $exists->execute([$p['slug']]);
        if ((int) $exists->fetchColumn() > 0) {
            continue;
        }
        $insert->execute([
            $p['slug'],
            $p['title'],
            'افزونه وردپرس',
            $p['image'],
            $p['heading_html'],
            $p['header'],
            'رایگان برای همه',
            'افزونهٔ وردپرس',
            $p['date_text'],
            (string) file_get_contents($dir . $p['file'] . '.body.html'),
            $p['title'],
            $p['meta_desc'],
            $p['sort'],
        ]);
        if ($category > 0) {
            $link->execute([(int) $pdo->lastInsertId(), $category]);
        }
    }
};
