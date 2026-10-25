<?php
/**
 * Longer, SEO-focused texts for the four client-site portfolio items (custom WordPress theme, SEO principles, owner guide).
 * An item is only rewritten while its title, description and body are still the originals from the earlier migrations,
 * so anything edited in the admin is left alone.
 */

declare(strict_types=1);

return static function (PDO $pdo): void {
    $sites = [
        'petfelix-website' => [
            'file'  => 'petfelix',
            'domain' => 'petfelix.ir',
            'title' => 'وب‌سایت petfelix.ir | وردپرس با قالب اختصاصی و سئوی فنی',
            'desc'  => 'نمونه‌کار وب‌سایت petfelix.ir؛ وردپرس با قالب اختصاصی، سریع و موبایل‌محور با رعایت اصول سئوی فنی و Core Web Vitals. طراحی: ماهان رضایی.',
        ],
        'cobaltwear-website' => [
            'file'  => 'cobaltwear',
            'domain' => 'cobaltwear.com',
            'title' => 'وب‌سایت cobaltwear.com | قالب اختصاصی وردپرس و ساختار سئو',
            'desc'  => 'نمونه‌کار وب‌سایت cobaltwear.com؛ وردپرس با قالب اختصاصی، آدرس‌های تمیز، داده‌های ساختاریافته و ساختار سئوپسند. طراحی: ماهان رضایی.',
        ],
        'iraniorkide-website' => [
            'file'  => 'iraniorkide',
            'domain' => 'iraniorkide.ir',
            'title' => 'وب‌سایت iraniorkide.ir | قالب اختصاصی وردپرس و سئوی محتوا',
            'desc'  => 'نمونه‌کار وب‌سایت iraniorkide.ir؛ وردپرس با قالب اختصاصی، ساختار تیترها و سئوی فنی و محتوایی مناسب فارسی. طراحی: ماهان رضایی.',
        ],
        'iranicert-website' => [
            'file'  => 'iranicert',
            'domain' => 'iranicert.com',
            'title' => 'وب‌سایت iranicert.com | قالب اختصاصی وردپرس، امنیت و اعتماد',
            'desc'  => 'نمونه‌کار وب‌سایت iranicert.com؛ وردپرس با قالب اختصاصی، HTTPS، دسترس‌پذیری و اصول اعتمادسازی برای سئو. طراحی: ماهان رضایی.',
        ],
    ];

    $dir    = __DIR__ . '/content/';
    $select = $pdo->prepare('SELECT id, title, meta_title, meta_desc, body_html FROM projects WHERE slug = ?');
    $update = $pdo->prepare('UPDATE projects SET body_html = ?, meta_title = ?, meta_desc = ? WHERE id = ?');

    foreach ($sites as $slug => $s) {
        $select->execute([$slug]);
        $row = $select->fetch(PDO::FETCH_ASSOC);
        $select->closeCursor();
        if (!$row) {
            continue;
        }

        $oldBody = (string) file_get_contents($dir . $s['file'] . '.body.html');
        $oldDesc = 'نمونه‌کار طراحی و توسعهٔ وب‌سایت ' . $s['domain'] . ' توسط ماهان رضایی؛ سایتی واکنش‌گرا و سریع برای موبایل و دسکتاپ.';
        $pristine = trim((string) $row['body_html']) === trim($oldBody)
            && $row['meta_title'] === $row['title']
            && $row['meta_desc'] === $oldDesc;
        if (!$pristine) {
            continue;
        }

        $update->execute([
            (string) file_get_contents($dir . $s['file'] . '.body2.html'),
            $s['title'],
            $s['desc'],
            (int) $row['id'],
        ]);
    }
};
