<?php
/**
 * Unique, correctly sized <title> and meta description for every page, project and article
 * (titles up to about 60 characters with the site name, descriptions 110 to 165).
 * Posts get their own meta_title column, so a long article headline no longer has to double as the tab title.
 * Only values that are still empty or still the earlier automatic text are replaced, so admin edits are kept.
 */

declare(strict_types=1);

return static function (PDO $pdo): void {
    $has = $pdo->query("SHOW COLUMNS FROM posts LIKE 'meta_title'");
    $exists = $has->fetch() !== false;
    $has->closeCursor();
    if (!$exists) {
        $pdo->exec("ALTER TABLE posts ADD COLUMN meta_title VARCHAR(300) NOT NULL DEFAULT '' AFTER body_html");
    }

    $pages = [
        'seo_index' => ['ماهان رضایی | توسعه‌دهندهٔ وب و مدیر IT (لاراول، PHP)',
            'ماهان رضایی، توسعه‌دهندهٔ وب و مدیر IT با تجربه در PHP، لاراول و وردپرس. نمونه‌کارها، افزونه‌های رایگان وردپرس، رزومه و راه تماس برای سفارش پروژه.'],
        'seo_resume' => ['رزومه ماهان رضایی | سوابق کاری، تحصیلات و مهارت‌ها',
            'رزومهٔ ماهان رضایی، مدیر IT و توسعه‌دهندهٔ وب: سوابق کاری، تحصیلات و مهارت‌ها در PHP، لاراول، وردپرس، پایتون، پایگاه داده و امنیت شبکه.'],
        'seo_portfolio' => ['نمونه‌کارهای ماهان رضایی | وب‌سایت، افزونه و شبکه',
            'نمونه‌کارهای طراحی و توسعهٔ وب‌سایت و وب‌اپلیکیشن، افزونه‌های رایگان وردپرس، داشبورد مدیریت، سامانهٔ چت و زیرساخت شبکه، همه از ماهان رضایی.'],
        'seo_blog' => ['وبلاگ ماهان رضایی | کاریابی، رزومه و طراحی سایت',
            'مقاله‌های آموزشی دربارهٔ پیدا کردن کار در حوزهٔ نرم‌افزار، نوشتن رزومهٔ حرفه‌ای و نقشهٔ راه یادگیری طراحی سایت از صفر، با تجربهٔ ماهان رضایی.'],
        'seo_contact' => ['تماس با ماهان رضایی | سفارش پروژه و مشاورهٔ IT',
            'برای سفارش طراحی سایت، توسعهٔ وب، افزونهٔ وردپرس و مشاورهٔ IT با ماهان رضایی تماس بگیرید؛ فرم تماس، ایمیل، تلفن و نقشهٔ موقعیت در همین صفحه است.'],
    ];

    $projects = [
        'veresk' => ['داشبورد مدیریت ورسک؛ React و Laravel',
            'نمونه‌کار داشبورد مدیریت یکپارچهٔ ورسک: سامانهٔ مدیریت آموزشی و مالی با React و Laravel، طراحی و توسعه توسط ماهان رضایی.'],
        'academia' => ['وب‌سایت آموزشگاه آکادمیا با وردپرس',
            'نمونه‌کار وب‌سایت آموزشگاه برنامه‌نویسی آکادمیا: یک پلتفرم آموزشی که با وردپرس طراحی و توسعه داده شده است، کار ماهان رضایی.'],
        'amozesh' => ['طراحی UI و UX وب‌سایت مدارس استان البرز',
            'نمونه‌کار طراحی رابط کاربری (UI) و تجربهٔ کاربری (UX) وب‌سایت مدارس آموزش و پرورش استان البرز، اثر ماهان رضایی.'],
        'oloom' => ['اتصال شبکهٔ رک ساختمان علوم پزشکی',
            'نمونه‌کار اتصال سایت‌های رک ساختمان علوم پزشکی: پروژهٔ شبکه و زیرساخت IT با اجرای ماهان رضایی و ملاحظات امنیتی زیرساخت.'],
        'mahi' => ['سامانهٔ ماهی؛ مدیریت انبار و فروش',
            'نمونه‌کار سامانهٔ ماهی: وب‌سایت جامع مدیریت انبار و گردش مالی برای ثبت، پیگیری و تحلیل موجودی کالا و اطلاعات مالی، اثر ماهان رضایی.'],
        'chatapp' => ['سامانهٔ چت آنلاین با PHP و MySQL',
            'نمونه‌کار سامانهٔ چت آنلاین (Live Wire) با PHP و MySQL؛ طراحی و پیاده‌سازی یک سیستم گفتگوی زنده توسط ماهان رضایی.'],
        'moaref-wordpress-plugin' => ['افزونهٔ وردپرس Moaref؛ کد معرف و کیف پول',
            'Moaref؛ افزونهٔ رایگان وردپرس و ووکامرس برای کد معرف، کمیسیون، کش‌بک و کیف پول تومانی. دانلود رایگان و ویرایش آزاد کد برای همه.'],
        'shainika-sms-auth-wordpress-plugin' => ['افزونهٔ ورود پیامکی وردپرس با ملی‌پیامک',
            'Shainika SMS Auth؛ افزونهٔ رایگان ورود و ثبت‌نام با کد یک‌بارمصرف پیامکی برای وردپرس و ووکامرس با ملی‌پیامک و چند سرویس دیگر.'],
        'petfelix-website' => ['وب‌سایت petfelix.ir | قالب اختصاصی وردپرس',
            'نمونه‌کار وب‌سایت petfelix.ir: وردپرس با قالب اختصاصی، سریع و موبایل‌محور، با رعایت اصول سئوی فنی و Core Web Vitals؛ کار ماهان رضایی.'],
        'cobaltwear-website' => ['وب‌سایت cobaltwear.com | قالب اختصاصی وردپرس',
            'نمونه‌کار وب‌سایت cobaltwear.com: وردپرس با قالب اختصاصی، آدرس‌های تمیز، داده‌های ساختاریافته و ساختار سئوپسند؛ کار ماهان رضایی.'],
        'iraniorkide-website' => ['وب‌سایت iraniorkide.ir | قالب اختصاصی وردپرس',
            'نمونه‌کار وب‌سایت iraniorkide.ir: وردپرس با قالب اختصاصی، ساختار درست تیترها و سئوی فنی و محتوایی مناسب زبان فارسی؛ کار ماهان رضایی.'],
        'iranicert-website' => ['وب‌سایت iranicert.com | قالب اختصاصی وردپرس',
            'نمونه‌کار وب‌سایت iranicert.com: وردپرس با قالب اختصاصی، HTTPS، دسترس‌پذیری و اصول اعتمادسازی برای سئو؛ کار ماهان رضایی.'],
    ];

    $posts = [
        'learn-web-design-roadmap' => ['نقشهٔ راه یادگیری طراحی سایت از صفر تا نمونه‌کار',
            'نقشهٔ راه گام‌به‌گام یادگیری طراحی سایت از صفر: HTML، CSS، JavaScript، اصول طراحی، روش‌های درست یادگیری و پنج پروژهٔ تمرینی برای ساخت نمونه‌کار.'],
        'professional-resume-principles' => ['اصول نوشتن رزومهٔ حرفه‌ای برای برنامه‌نویسان',
            'ساختار درست رزومه، نوشتن دستاورد به‌جای وظیفه، ظاهر ساده و خوانا، اشتباه‌های رایج و چک‌لیست نهایی پیش از ارسال، برای برنامه‌نویسان و متخصصان IT.'],
        'job-hunting-guide' => ['نقشهٔ راه پیدا کردن کار در حوزهٔ نرم‌افزار و IT',
            'راهنمای کامل پیدا کردن کار در حوزهٔ نرم‌افزار و IT: تعیین هدف، رزومه، جست‌وجوی آگهی، مصاحبه، مذاکرهٔ قرارداد و برنامهٔ سی‌روزهٔ کاریابی.'],
    ];

    // Previous automatic values (plus empty): anything else was typed by a person and stays.
    $wasPage = [
        'seo_blog_description' => 'مقالات آموزشی دربارهٔ پیدا کردن کار در حوزهٔ نرم‌افزار، نوشتن رزومهٔ حرفه‌ای و یادگیری طراحی سایت.',
        'seo_blog_title' => 'وبلاگ ماهان رضایی | مقالات کاریابی، رزومه و طراحی سایت',
        'seo_contact_description' => 'برای سفارش طراحی سایت، توسعهٔ وب و مشاورهٔ IT با ماهان رضایی تماس بگیرید: فرم تماس، ایمیل و شمارهٔ تلفن.',
        'seo_contact_title' => 'تماس با ماهان رضایی | سفارش پروژه و همکاری',
        'seo_index_description' => 'ماهان رضایی، مدیر IT و توسعه‌دهندهٔ وب با تجربه در PHP، لاراول و وردپرس. نمونه‌کارها، افزونه‌های رایگان وردپرس، رزومه و راه‌های تماس برای سفارش پروژه.',
        'seo_index_title' => 'ماهان رضایی | مدیر IT و توسعه‌دهندهٔ وب (لاراول، PHP، وردپرس)',
        'seo_portfolio_description' => 'نمونه‌کارهای طراحی و توسعهٔ وب، افزونه‌های رایگان وردپرس، داشبورد مدیریت، سامانهٔ چت و زیرساخت شبکه از ماهان رضایی.',
        'seo_portfolio_title' => 'نمونه‌کارهای ماهان رضایی | طراحی وب، وردپرس و شبکه',
        'seo_resume_description' => 'رزومهٔ ماهان رضایی: سوابق کاری در مدیریت IT و توسعهٔ وب، تحصیلات و مهارت‌ها در PHP، لاراول، وردپرس، پایتون و امنیت شبکه.',
        'seo_resume_title' => 'رزومه ماهان رضایی | تجربه کاری، تحصیلات و مهارت‌ها',
    ];
    $wasProject = [
        'veresk' => ['طراحی داشبورد ادمین ورسک', ''],
        'academia' => ['طراحی وبسایت آکادمیا', ''],
        'amozesh' => ['طراحی وبسایت مدارس آموزش و پروش کل استان البرز', ''],
        'ershad' => ['نگهداری و توسعه شبکه ساختمان ارشاد کل استان البرز', ''],
        'oloom' => ['اتصال سایت های رک ساختمان علوم پزشکی', ''],
        'mahi' => ['طراحی وبسایت انبار داری و مدیریت فروش', ''],
        'sisishop' => ['طراحی سایت آنلاین شاپ سیسی شاپ', ''],
        'shainika' => ['طراحی سایت فروشگاهی کالای خواب شاینیکا', ''],
        'chatapp' => ['طراحی سامانه چت live wire در محیط php', ''],
        'moaref-wordpress-plugin' => ['افزونهٔ وردپرس کد معرف و کیف پول (Moaref)', 'Moaref؛ افزونهٔ رایگان وردپرس و ووکامرس برای کد معرف، کمیسیون، کش‌بک و کیف پول تومانی. دانلود رایگان و ویرایش آزاد کد.'],
        'shainika-sms-auth-wordpress-plugin' => ['افزونهٔ ورود پیامکی وردپرس با ملی‌پیامک (Shainika SMS Auth)', 'Shainika SMS Auth؛ افزونهٔ رایگان ورود و ثبت‌نام با کد یک‌بارمصرف پیامکی برای وردپرس و ووکامرس با ملی‌پیامک و چند سرویس دیگر. دانلود رایگان.'],
        'petfelix-website' => ['وب‌سایت petfelix.ir | وردپرس با قالب اختصاصی و سئوی فنی', 'نمونه‌کار وب‌سایت petfelix.ir؛ وردپرس با قالب اختصاصی، سریع و موبایل‌محور با رعایت اصول سئوی فنی و Core Web Vitals. طراحی: ماهان رضایی.'],
        'cobaltwear-website' => ['وب‌سایت cobaltwear.com | قالب اختصاصی وردپرس و ساختار سئو', 'نمونه‌کار وب‌سایت cobaltwear.com؛ وردپرس با قالب اختصاصی، آدرس‌های تمیز، داده‌های ساختاریافته و ساختار سئوپسند. طراحی: ماهان رضایی.'],
        'iraniorkide-website' => ['وب‌سایت iraniorkide.ir | قالب اختصاصی وردپرس و سئوی محتوا', 'نمونه‌کار وب‌سایت iraniorkide.ir؛ وردپرس با قالب اختصاصی، ساختار تیترها و سئوی فنی و محتوایی مناسب فارسی. طراحی: ماهان رضایی.'],
        'iranicert-website' => ['وب‌سایت iranicert.com | قالب اختصاصی وردپرس، امنیت و اعتماد', 'نمونه‌کار وب‌سایت iranicert.com؛ وردپرس با قالب اختصاصی، HTTPS، دسترس‌پذیری و اصول اعتمادسازی برای سئو. طراحی: ماهان رضایی.'],
    ];
    $wasPost = [
        'post-1' => '',
        'post-2' => '',
        'post-3' => '',
        'post-4' => '',
        'post-5' => '',
        'post-6' => '',
        'learn-web-design-roadmap' => 'مسیر گام‌به‌گام یادگیری طراحی سایت از صفر: HTML، CSS، JavaScript، اصول طراحی، روش‌های درست یادگیری و پنج پروژهٔ تمرینی.',
        'professional-resume-principles' => 'ساختار درست رزومه، نوشتن دستاورد به‌جای وظیفه، ظاهر رزومه، اشتباه‌های رایج و چک‌لیست نهایی پیش از ارسال برای متخصصان نرم‌افزار و IT.',
        'job-hunting-guide' => 'نقشهٔ کامل پیدا کردن کار در حوزهٔ نرم‌افزار و IT: از تعیین هدف و آماده‌سازی رزومه تا مصاحبه، مذاکره و برنامهٔ سی‌روزهٔ کاریابی.',
    ];

    $setting = $pdo->prepare('SELECT v FROM settings WHERE k = ?');
    $write   = $pdo->prepare('UPDATE settings SET v = ? WHERE k = ?');
    foreach ($pages as $key => [$title, $desc]) {
        foreach ([$key . '_title' => $title, $key . '_description' => $desc] as $name => $value) {
            $setting->execute([$name]);
            $current = $setting->fetchColumn();
            $setting->closeCursor();
            if ($current === false) {
                $pdo->prepare('INSERT INTO settings (k, v) VALUES (?, ?)')->execute([$name, $value]);
            } elseif ($current === '' || $current === ($wasPage[$name] ?? null)) {
                $write->execute([$value, $name]);
            }
        }
    }

    $project = $pdo->prepare('SELECT id, title, meta_title, meta_desc FROM projects WHERE slug = ?');
    $setProject = $pdo->prepare('UPDATE projects SET meta_title = ?, meta_desc = ? WHERE id = ?');
    foreach ($projects as $slug => [$title, $desc]) {
        $project->execute([$slug]);
        $r = $project->fetch(PDO::FETCH_ASSOC);
        $project->closeCursor();
        if (!$r) {
            continue;
        }
        $mt = ($r['meta_title'] === '' || $r['meta_title'] === $r['title'] || $r['meta_title'] === ($wasProject[$slug][0] ?? null)) ? $title : $r['meta_title'];
        $md = ($r['meta_desc'] === '' || $r['meta_desc'] === ($wasProject[$slug][1] ?? null)) ? $desc : $r['meta_desc'];
        $setProject->execute([$mt, $md, (int) $r['id']]);
    }

    $post = $pdo->prepare('SELECT id, meta_title, meta_desc FROM posts WHERE slug = ?');
    $setPost = $pdo->prepare('UPDATE posts SET meta_title = ?, meta_desc = ? WHERE id = ?');
    foreach ($posts as $slug => [$title, $desc]) {
        $post->execute([$slug]);
        $r = $post->fetch(PDO::FETCH_ASSOC);
        $post->closeCursor();
        if (!$r) {
            continue;
        }
        $mt = $r['meta_title'] === '' ? $title : $r['meta_title'];
        $md = ($r['meta_desc'] === '' || $r['meta_desc'] === ($wasPost[$slug] ?? null)) ? $desc : $r['meta_desc'];
        $setPost->execute([$mt, $md, (int) $r['id']]);
    }
};
