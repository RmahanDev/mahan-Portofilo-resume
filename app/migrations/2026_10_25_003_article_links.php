<?php
/**
 * Contextual internal links at the end of the three articles: related projects, related articles and the resume/contact pages.
 * Added once (the block carries a data-links marker) and only to articles that still exist.
 */

declare(strict_types=1);

return static function (PDO $pdo): void {
    $blocks = [
        'learn-web-design-roadmap' => [
            'intro' => 'برای دیدن اینکه این مسیر در عمل به چه چیزی می‌رسد، چند نمونهٔ واقعی را ببینید:',
            'items' => [
                '<a href="../Projects/veresk">داشبورد مدیریت ورسک</a>، یک سامانهٔ مدیریت آموزشی و مالی با React و Laravel',
                '<a href="../Projects/chatapp">سامانهٔ چت آنلاین</a> با PHP و MySQL، مناسب برای تمرین بک‌اند و پایگاه داده',
                '<a href="../Projects/mahi">سامانهٔ مدیریت انبار و فروش ماهی</a>، نمونه‌ای از یک سیستم مدیریتی کامل',
                '<a href="../Projects/academia">وب‌سایت آموزشگاه آکادمیا</a> که با وردپرس ساخته شده است',
                'برای مرحلهٔ بعد: <a href="../Blogs/professional-resume-principles">اصول نوشتن رزومهٔ حرفه‌ای</a> و <a href="../Blogs/job-hunting-guide">نقشهٔ راه پیدا کردن کار در حوزهٔ نرم‌افزار</a>',
                '<a href="../portfolio">همهٔ نمونه‌کارها</a> را ببینید یا از <a href="../contact">صفحهٔ تماس</a> سؤال بپرسید',
            ],
        ],
        'professional-resume-principles' => [
            'intro' => 'بعد از نوشتن رزومه، این صفحه‌ها و مطلب‌ها به کارتان می‌آیند:',
            'items' => [
                '<a href="../resume">رزومهٔ ماهان رضایی</a> را به‌عنوان یک نمونهٔ کامل و ساختارمند ببینید',
                '<a href="../Blogs/job-hunting-guide">نقشهٔ راه پیدا کردن کار</a>: از رزومه تا مصاحبه و قرارداد',
                '<a href="../portfolio">نمونه‌کارها</a> بخشی از رزومه‌اند؛ پروژه‌های واقعی از همین سایت را ببینید',
                'اگر هنوز مهارت‌تان کامل نیست، <a href="../Blogs/learn-web-design-roadmap">نقشهٔ راه یادگیری طراحی سایت</a> را بخوانید',
                'برای مشورت یا همکاری، <a href="../contact">با من تماس بگیرید</a>',
            ],
        ],
        'job-hunting-guide' => [
            'intro' => 'برای هر مرحلهٔ این نقشه، منبع مرتبط را اینجا پیدا می‌کنید:',
            'items' => [
                'نوشتن رزومه: <a href="../Blogs/professional-resume-principles">اصول نوشتن رزومهٔ حرفه‌ای برای برنامه‌نویسان و متخصصان IT</a>',
                'تقویت مهارت‌ها: <a href="../Blogs/learn-web-design-roadmap">نقشهٔ راه یادگیری طراحی سایت از صفر</a>',
                'نمونهٔ یک رزومهٔ کامل: <a href="../resume">رزومهٔ ماهان رضایی</a>',
                'نمونه‌کار برای رزومه: <a href="../portfolio">پروژه‌های واقعی و افزونه‌های رایگان</a>',
                'سؤال یا همکاری: <a href="../contact">صفحهٔ تماس</a>',
            ],
        ],
    ];

    $select = $pdo->prepare('SELECT id, body_html FROM posts WHERE slug = ?');
    $update = $pdo->prepare('UPDATE posts SET body_html = ? WHERE id = ?');

    foreach ($blocks as $slug => $b) {
        $select->execute([$slug]);
        $post = $select->fetch(PDO::FETCH_ASSOC);
        $select->closeCursor();
        if (!$post || strpos((string) $post['body_html'], 'data-links="related"') !== false) {
            continue;
        }

        $html = "\n<div class=\"callout\" data-links=\"related\">\n"
            . "<strong class=\"callout__title\">مطالب و نمونه‌کارهای مرتبط</strong>\n"
            . '<p>' . $b['intro'] . "</p>\n<ul>\n";
        foreach ($b['items'] as $item) {
            $html .= '<li>' . $item . "</li>\n";
        }
        $html .= "</ul>\n</div>\n";

        // Inside the closing caption-post wrapper when the article has one, so the block matches the text column.
        $body = rtrim((string) $post['body_html']);
        $wrapped = substr($body, -6) === '</div>' && strpos(ltrim($body), '<div class="caption-post">') === 0;
        $body = $wrapped ? substr($body, 0, -strlen('</div>')) . $html . '</div>' : $body . "\n" . $html;
        $update->execute([$body, (int) $post['id']]);
    }
};
