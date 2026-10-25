<?php
/**
 * Two client websites as portfolio items: iraniorkide.ir and iranicert.com.
 * Items that already exist (same slug) are left untouched, so text edited in the admin is never overwritten.
 */

declare(strict_types=1);

return static function (PDO $pdo): void {
    $projects = [
        [
            'slug'         => 'iraniorkide-website',
            'file'         => 'iraniorkide',
            'title'        => 'وب‌سایت iraniorkide.ir',
            'heading_html' => 'وب‌سایت iraniorkide.ir<br/>طراحی و توسعه وب',
            'image'        => 'assets/images/projects/iraniorkide.jpg',
            'header'       => 'assets/images/projects/iraniorkide-header.jpg',
            'meta_desc'    => 'نمونه‌کار طراحی و توسعهٔ وب‌سایت iraniorkide.ir توسط ماهان رضایی؛ سایتی واکنش‌گرا و سریع برای موبایل و دسکتاپ.',
        ],
        [
            'slug'         => 'iranicert-website',
            'file'         => 'iranicert',
            'title'        => 'وب‌سایت iranicert.com',
            'heading_html' => 'وب‌سایت iranicert.com<br/>طراحی و توسعه وب',
            'image'        => 'assets/images/projects/iranicert.jpg',
            'header'       => 'assets/images/projects/iranicert-header.jpg',
            'meta_desc'    => 'نمونه‌کار طراحی و توسعهٔ وب‌سایت iranicert.com توسط ماهان رضایی؛ سایتی واکنش‌گرا و سریع برای موبایل و دسکتاپ.',
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
