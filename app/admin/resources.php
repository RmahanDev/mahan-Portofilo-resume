<?php
/**
 * What the admin panel can edit.
 *
 * resources(): list-type content (one DB table each) handled by admin/list.php and admin/edit.php.
 * setting_groups(): single values stored in the `settings` table, edited by admin/settings.php.
 *
 * Field types: text, textarea, html, image, number, percent, bool, select, slug, categories, gallery.
 */

declare(strict_types=1);

const FEATHER_SOCIAL_ICONS = [
    'feathericon-linkedin'  => 'LinkedIn',
    'feathericon-github'    => 'GitHub',
    'feathericon-instagram' => 'Instagram',
    'feathericon-twitter'   => 'Twitter / X',
    'feathericon-facebook'  => 'Facebook',
    'feathericon-youtube'   => 'YouTube',
    'feathericon-send'      => 'Telegram',
    'feathericon-mail'      => 'Email',
    'feathericon-globe'     => 'Website',
    'feathericon-gitlab'    => 'GitLab',
];

function resources(): array
{
    return [
        'projects' => [
            'table'    => 'projects',
            'title'    => 'نمونه کارها',
            'singular' => 'نمونه کار',
            'icon'     => 'feathericon-briefcase',
            'list'     => ['cover' => 'image', 'title' => 'عنوان', 'card_category' => 'دسته', 'is_published' => 'وضعیت'],
            'view_url' => 'Projects/{slug}',
            'fields'   => [
                'title'          => ['label' => 'عنوان (روی کارت صفحه نمونه کارها)', 'type' => 'text', 'required' => true],
                'slug'           => ['label' => 'آدرس صفحه (Projects/...)', 'type' => 'slug', 'from' => 'title', 'help' => 'فقط حروف انگلیسی، عدد و خط تیره. مثلا: veresk'],
                'cover'          => ['label' => 'تصویر کارت', 'type' => 'image'],
                'card_category'  => ['label' => 'برچسب دسته روی کارت', 'type' => 'text', 'help' => 'اختیاری. اگر خالی بماند، نام دستهٔ اصلی (اولین دسته در ترتیب دسته‌بندی‌ها) نمایش داده می‌شود.'],
                'categories'     => ['label' => 'دسته‌بندی‌ها (برای فیلتر)', 'type' => 'categories'],
                'heading_html'   => ['label' => 'تیتر صفحه پروژه', 'type' => 'text', 'help' => 'برای رفتن به خط بعد از &lt;br&gt; استفاده کنید.', 'raw' => true],
                'header_image'   => ['label' => 'تصویر بالای صفحه پروژه', 'type' => 'image'],
                'client'         => ['label' => 'مشتری', 'type' => 'text', 'col' => 4],
                'category_label' => ['label' => 'دسته بندی (صفحه پروژه)', 'type' => 'text', 'col' => 4, 'help' => 'اختیاری؛ خالی = نام همهٔ دسته‌ها'],
                'date_text'      => ['label' => 'تاریخ', 'type' => 'text', 'col' => 4, 'default' => 'jalali_today'],
                'body_html'      => ['label' => 'توضیحات پروژه', 'type' => 'html'],
                'images'         => ['label' => 'گالری تصاویر (اسلایدر)', 'type' => 'gallery'],
                'meta_title'     => ['label' => 'عنوان تب مرورگر (SEO)', 'type' => 'text', 'col' => 6],
                'meta_desc'      => ['label' => 'توضیحات متا (SEO)', 'type' => 'text', 'col' => 6],
                'is_published'   => ['label' => 'نمایش در سایت', 'type' => 'bool', 'default' => 1],
            ],
        ],
        'categories' => [
            'table'    => 'categories',
            'title'    => 'دسته‌بندی نمونه کارها',
            'singular' => 'دسته‌بندی',
            'icon'     => 'feathericon-layers',
            'list'     => ['name' => 'نام', 'slug' => 'شناسه'],
            'fields'   => [
                'name' => ['label' => 'نام (در فیلتر)', 'type' => 'text', 'required' => true],
                'slug' => ['label' => 'شناسه انگلیسی', 'type' => 'slug', 'from' => 'name', 'help' => 'مثلا website یا wordpress'],
                'sort' => ['label' => 'ترتیب', 'type' => 'number', 'col' => 4, 'help' => 'ترتیب نمایش در فیلتر. اولین دستهٔ هر نمونه‌کار، دستهٔ اصلی آن است و روی کارتش نوشته می‌شود؛ پس دسته‌های «نوع کار» (وب‌سایت، افزونه، ...) را قبل از دسته‌های «فناوری» (وردپرس، ...) بگذارید.'],
            ],
        ],
        'posts' => [
            'table'    => 'posts',
            'title'    => 'وبلاگ',
            'singular' => 'پست',
            'icon'     => 'feathericon-edit',
            'list'     => ['cover' => 'image', 'title' => 'عنوان', 'date_text' => 'تاریخ', 'is_published' => 'وضعیت'],
            'view_url' => 'Blogs/{slug}',
            'fields'   => [
                'title'        => ['label' => 'عنوان', 'type' => 'text', 'required' => true],
                'slug'         => ['label' => 'آدرس صفحه (Blogs/...)', 'type' => 'slug', 'from' => 'title'],
                'category'     => ['label' => 'دسته', 'type' => 'text', 'col' => 6],
                'date_text'    => ['label' => 'تاریخ', 'type' => 'text', 'col' => 6, 'default' => 'jalali_today'],
                'cover'        => ['label' => 'تصویر کارت (صفحه وبلاگ)', 'type' => 'image'],
                'excerpt'      => ['label' => 'خلاصه (روی کارت)', 'type' => 'textarea'],
                'intro_html'   => ['label' => 'مقدمه (زیر تیتر)', 'type' => 'html', 'rows' => 5],
                'header_image' => ['label' => 'تصویر بالای پست', 'type' => 'image'],
                'body_html'    => ['label' => 'متن پست', 'type' => 'html', 'rows' => 18],
                'meta_desc'    => ['label' => 'توضیحات متا (SEO)', 'type' => 'text'],
                'is_published' => ['label' => 'منتشر شود', 'type' => 'bool', 'default' => 1],
            ],
        ],
        'services' => [
            'table'    => 'services',
            'title'    => 'چه کاری انجام می‌دهم',
            'singular' => 'خدمت',
            'icon'     => 'feathericon-grid',
            'group'    => 'about',
            'list'     => ['icon' => 'image', 'title' => 'عنوان'],
            'fields'   => [
                'title'       => ['label' => 'عنوان', 'type' => 'text', 'required' => true],
                'description' => ['label' => 'توضیح', 'type' => 'textarea'],
                'icon'        => ['label' => 'آیکون', 'type' => 'image', 'default' => 'assets/icons/icon-dev.svg'],
                'icon_alt'    => ['label' => 'متن جایگزین آیکون', 'type' => 'text'],
            ],
        ],
        'skills' => [
            'table'    => 'skills',
            'title'    => 'مهارت‌های فنی (صفحه درباره من)',
            'singular' => 'مهارت',
            'icon'     => 'feathericon-check-circle',
            'group'    => 'about',
            'list'     => ['text' => 'متن'],
            'fields'   => [
                'text' => ['label' => 'متن مهارت', 'type' => 'text', 'required' => true],
            ],
        ],
        'experiences' => [
            'table'    => 'experiences',
            'title'    => 'خلاصه تجربه کاری (اسلایدر)',
            'singular' => 'تجربه',
            'icon'     => 'feathericon-message-square',
            'group'    => 'about',
            'list'     => ['avatar' => 'image', 'name' => 'مجموعه', 'role' => 'سمت'],
            'fields'   => [
                'name'      => ['label' => 'نام مجموعه', 'type' => 'text', 'required' => true],
                'role'      => ['label' => 'سمت (داخل پاپ‌آپ)', 'type' => 'text'],
                'avatar'    => ['label' => 'لوگو / آواتار', 'type' => 'image'],
                'caption'   => ['label' => 'متن کوتاه روی اسلاید', 'type' => 'textarea'],
                'body_html' => ['label' => 'متن کامل پاپ‌آپ', 'type' => 'html', 'rows' => 6],
            ],
        ],
        'resume_items' => [
            'table'    => 'resume_items',
            'title'    => 'تحصیلات و سوابق',
            'singular' => 'آیتم رزومه',
            'icon'     => 'feathericon-book-open',
            'group'    => 'resume',
            'list'     => ['section' => 'بخش', 'title' => 'عنوان', 'period' => 'زیرعنوان'],
            'filter'   => 'section',
            'fields'   => [
                'section'     => ['label' => 'بخش', 'type' => 'select', 'options' => ['education' => 'تحصیلات و مدارک', 'experience' => 'سوابق شغلی و پروژه‌ها']],
                'title'       => ['label' => 'عنوان', 'type' => 'text', 'required' => true],
                'period'      => ['label' => 'زیرعنوان (محل / دوره)', 'type' => 'text'],
                'description' => ['label' => 'توضیحات', 'type' => 'textarea', 'rows' => 5],
            ],
        ],
        'socials' => [
            'table'    => 'socials',
            'title'    => 'شبکه‌های اجتماعی',
            'singular' => 'لینک اجتماعی',
            'icon'     => 'feathericon-share-2',
            'group'    => 'profile',
            'list'     => ['icon' => 'نوع', 'url' => 'لینک'],
            'fields'   => [
                'icon' => ['label' => 'نوع', 'type' => 'select', 'options' => FEATHER_SOCIAL_ICONS],
                'url'  => ['label' => 'لینک', 'type' => 'text', 'required' => true, 'dir' => 'ltr'],
            ],
        ],
    ];
}

function resource(string $key): array
{
    $all = resources();
    if (!isset($all[$key])) {
        http_response_code(404);
        exit('Unknown section');
    }
    return $all[$key] + ['key' => $key];
}

function setting_groups(): array
{
    return [
        'profile' => [
            'title' => 'پروفایل و سایدبار',
            'icon'  => 'feathericon-user',
            'fields' => [
                'profile_name'        => ['label' => 'نام', 'type' => 'text', 'col' => 6],
                'profile_badge'       => ['label' => 'عنوان زیر نام', 'type' => 'text', 'col' => 6],
                'profile_avatar'      => ['label' => 'عکس پروفایل', 'type' => 'image'],
                'contact_email'       => ['label' => 'ایمیل', 'type' => 'text', 'col' => 6, 'dir' => 'ltr'],
                'contact_phone'       => ['label' => 'تلفن', 'type' => 'text', 'col' => 6, 'dir' => 'ltr'],
                'work_status'         => ['label' => 'وضعیت همکاری', 'type' => 'text', 'col' => 6],
                'location'            => ['label' => 'موقعیت مکانی', 'type' => 'text', 'col' => 6],
                'sidebar_toggle_text' => ['label' => 'متن دکمه موبایل', 'type' => 'text'],
            ],
            'related' => ['socials'],
        ],
        'about' => [
            'title' => 'صفحه درباره من',
            'icon'  => 'feathericon-home',
            'fields' => [
                'about_title'      => ['label' => 'تیتر صفحه', 'type' => 'text'],
                'about_html'       => ['label' => 'متن معرفی', 'type' => 'html', 'rows' => 8],
                'services_title'   => ['label' => 'تیتر بخش خدمات', 'type' => 'text', 'col' => 4],
                'skills_title'     => ['label' => 'تیتر بخش مهارت‌ها', 'type' => 'text', 'col' => 4],
                'experience_title' => ['label' => 'تیتر بخش تجربه کاری', 'type' => 'text', 'col' => 4],
            ],
            'related' => ['services', 'skills', 'experiences'],
        ],
        'resume' => [
            'title' => 'صفحه رزومه',
            'icon'  => 'feathericon-file-text',
            'fields' => [
                'resume_title'            => ['label' => 'تیتر صفحه', 'type' => 'text', 'col' => 6],
                'resume_education_title'  => ['label' => 'تیتر بخش تحصیلات', 'type' => 'text', 'col' => 6],
                'resume_experience_title' => ['label' => 'تیتر بخش سوابق', 'type' => 'text', 'col' => 6],
                'resume_skills_title'     => ['label' => 'تیتر بخش مهارت‌ها', 'type' => 'text', 'col' => 6],
            ],
            'related' => ['resume_items'],
        ],
        'portfolio' => [
            'title' => 'صفحه نمونه کارها',
            'icon'  => 'feathericon-image',
            'fields' => [
                'portfolio_title'              => ['label' => 'تیتر صفحه', 'type' => 'text', 'col' => 6],
                'portfolio_filter_placeholder' => ['label' => 'متن انتخاب دسته (موبایل)', 'type' => 'text', 'col' => 6],
                'portfolio_filter_all'         => ['label' => 'متن فیلتر «همه»', 'type' => 'text', 'col' => 4],
                'project_page_title'           => ['label' => 'تیتر صفحه هر پروژه', 'type' => 'text', 'col' => 4],
                'portfolio_back_text'          => ['label' => 'متن دکمه بازگشت', 'type' => 'text', 'col' => 4],
            ],
            'related' => ['projects', 'categories'],
        ],
        'blog' => [
            'title' => 'صفحه وبلاگ',
            'icon'  => 'feathericon-edit-3',
            'fields' => [
                'blog_title'      => ['label' => 'تیتر صفحه', 'type' => 'text', 'col' => 4],
                'post_page_title' => ['label' => 'تیتر صفحه هر پست', 'type' => 'text', 'col' => 4],
                'blog_empty_text' => ['label' => 'متن وقتی پستی منتشر نشده', 'type' => 'text', 'col' => 4],
            ],
            'related' => ['posts'],
        ],
        'contact' => [
            'title' => 'صفحه تماس',
            'icon'  => 'feathericon-map-pin',
            'fields' => [
                'contact_title'        => ['label' => 'تیتر صفحه', 'type' => 'text', 'col' => 6],
                'contact_form_title'   => ['label' => 'تیتر فرم', 'type' => 'text', 'col' => 6],
                'contact_success_text' => ['label' => 'پیام بعد از ارسال موفق فرم', 'type' => 'text'],
                'map_lat'              => ['label' => 'عرض جغرافیایی (lat)', 'type' => 'text', 'col' => 4, 'dir' => 'ltr'],
                'map_lng'              => ['label' => 'طول جغرافیایی (lng)', 'type' => 'text', 'col' => 4, 'dir' => 'ltr'],
                'map_zoom'             => ['label' => 'زوم نقشه', 'type' => 'number', 'col' => 4],
                'map_popup'            => ['label' => 'متن روی نشانگر نقشه', 'type' => 'text'],
            ],
        ],
        'seo' => [
            'title' => 'منو و سئو',
            'icon'  => 'feathericon-search',
            'fields' => [
                'nav_about'                => ['label' => 'منو: درباره من', 'type' => 'text', 'col' => 4],
                'nav_resume'               => ['label' => 'منو: رزومه', 'type' => 'text', 'col' => 4],
                'nav_portfolio'            => ['label' => 'منو: نمونه کارها', 'type' => 'text', 'col' => 4],
                'nav_blog'                 => ['label' => 'منو: وبلاگ', 'type' => 'text', 'col' => 6],
                'nav_contact'              => ['label' => 'منو: تماس', 'type' => 'text', 'col' => 6],
                'site_author'              => ['label' => 'نام سایت / نویسنده (متا)', 'type' => 'text', 'col' => 6, 'dir' => 'ltr'],
                'site_description'         => ['label' => 'توضیحات پیش‌فرض سایت', 'type' => 'text', 'col' => 6],
                'seo_index_title'          => ['label' => 'عنوان تب: درباره من', 'type' => 'text', 'col' => 6],
                'seo_index_description'    => ['label' => 'توضیحات: درباره من', 'type' => 'text', 'col' => 6],
                'seo_resume_title'         => ['label' => 'عنوان تب: رزومه', 'type' => 'text', 'col' => 6],
                'seo_resume_description'   => ['label' => 'توضیحات: رزومه', 'type' => 'text', 'col' => 6],
                'seo_portfolio_title'      => ['label' => 'عنوان تب: نمونه کارها', 'type' => 'text', 'col' => 6],
                'seo_portfolio_description' => ['label' => 'توضیحات: نمونه کارها', 'type' => 'text', 'col' => 6],
                'seo_blog_title'           => ['label' => 'عنوان تب: وبلاگ', 'type' => 'text', 'col' => 6],
                'seo_blog_description'     => ['label' => 'توضیحات: وبلاگ', 'type' => 'text', 'col' => 6],
                'seo_contact_title'        => ['label' => 'عنوان تب: تماس', 'type' => 'text', 'col' => 6],
                'seo_contact_description'  => ['label' => 'توضیحات: تماس', 'type' => 'text', 'col' => 6],
                'site_url'                 => ['label' => 'آدرس اصلی سایت (مثلا https://example.ir)', 'type' => 'text', 'col' => 12, 'dir' => 'ltr'],
                'seo_google_verification'  => ['label' => 'کد تایید گوگل سرچ کنسول (فقط مقدار content)', 'type' => 'text', 'col' => 6, 'dir' => 'ltr'],
                'seo_bing_verification'    => ['label' => 'کد تایید بینگ (فقط مقدار content)', 'type' => 'text', 'col' => 6, 'dir' => 'ltr'],
            ],
        ],
    ];
}
