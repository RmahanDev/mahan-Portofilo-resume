<?php
/**
 * Two client websites as portfolio items: petfelix.ir and cobaltwear.com.
 * Items that already exist (same slug) are left untouched, so text edited in the admin is never overwritten.
 */

declare(strict_types=1);

return static function (PDO $pdo): void {
    $projects = [
        [
            'slug'         => 'petfelix-website',
            'file'         => 'petfelix',
            'title'        => 'وب‌سایت petfelix.ir',
            'heading_html' => 'وب‌سایت petfelix.ir<br/>طراحی و توسعه وب',
            'image'        => 'assets/images/projects/petfelix.jpg',
            'header'       => 'assets/images/projects/petfelix-header.jpg',
            'meta_desc'    => 'نمونه‌کار طراحی و توسعهٔ وب‌سایت petfelix.ir توسط ماهان رضایی؛ سایتی واکنش‌گرا و سریع برای موبایل و دسکتاپ.',
        ],
        [
            'slug'         => 'cobaltwear-website',
            'file'         => 'cobaltwear',
            'title'        => 'وب‌سایت cobaltwear.com',
            'heading_html' => 'وب‌سایت cobaltwear.com<br/>طراحی و توسعه وب',
            'image'        => 'assets/images/projects/cobaltwear.jpg',
            'header'       => 'assets/images/projects/cobaltwear-header.jpg',
            'meta_desc'    => 'نمونه‌کار طراحی و توسعهٔ وب‌سایت cobaltwear.com توسط ماهان رضایی؛ سایتی واکنش‌گرا و سریع برای موبایل و دسکتاپ.',
        ],
    ];

    $lookup = $pdo->query("SELECT id FROM categories WHERE slug = 'web-development'");
    $category = (int) $lookup->fetchColumn();
    $lookup->closeCursor();
    $exists = $pdo->prepare('SELECT COUNT(*) FROM projects WHERE slug = ?');
    $insert = $pdo->prepare(
        'INSERT INTO projects (slug, title, card_category, cover, heading_html, header_image, client, category_label, date_text, body_html, meta_title, meta_desc, is_published, sort)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)'
    );
    $link = $pdo->prepare('INSERT INTO project_categories (project_id, category_id) VALUES (?, ?)');
    $dir  = __DIR__ . '/content/';

    foreach ($projects as $p) {
        $exists->execute([$p['slug']]);
        $found = (int) $exists->fetchColumn();
        $exists->closeCursor();
        if ($found > 0) {
            continue;
        }
        $insert->execute([
            $p['slug'],
            $p['title'],
            'طراحی وب',
            $p['image'],
            $p['heading_html'],
            $p['header'],
            '',
            'طراحی وب',
            '',
            (string) file_get_contents($dir . $p['file'] . '.body.html'),
            $p['title'],
            $p['meta_desc'],
            0,
        ]);
        if ($category > 0) {
            $link->execute([(int) $pdo->lastInsertId(), $category]);
        }
    }
};
