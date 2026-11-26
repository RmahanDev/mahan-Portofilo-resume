<?php
/**
 * Article: Claude API or subscription (cost, account and verification risks for Iranian readers, who each option suits).
 * Added once per slug, so later edits in the admin panel are never overwritten.
 */

declare(strict_types=1);

return static function (PDO $pdo): void {
    $slug = 'claude-api-vs-subscription';
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
        'کلاد API یا اشتراک؛ کدام برای شما مناسب‌تر است؟',
        'هوش مصنوعی',
        '۵ آذر ۱۴۰۵',
        'assets/images/news/claude-api-vs-subscription-cover.jpg',
        'اشتراک ۲۰ دلاری یا API؟ مقایسهٔ هزینه و کاربرد، ریسک‌های اکانت اشتراکی برای کاربران ایرانی، روش کنترل هزینهٔ API و پیشنهاد انتخاب بر اساس نیاز شما.',
        (string) file_get_contents($dir . 'claude-api-vs-subscription.intro.html'),
        'assets/images/news/claude-api-vs-subscription-header.jpg',
        (string) file_get_contents($dir . 'claude-api-vs-subscription.body.html'),
        'Claude API یا اشتراک؛ مقایسه، ریسک‌ها و انتخاب درست',
        'مقایسهٔ Claude API و اشتراک ۲۰ دلاری، ریسک اکانت اشتراکی و مدارک جعلی، و روش کنترل هزینهٔ API؛ همراه با پیشنهاد انتخاب بر اساس نوع کاربر.',
    ]);
};
