<?php
/**
 * Page head + sidebar + top menu, shared by every public page.
 *
 * @var string $prefix      "" for root pages, "../" for /Projects/* and /Blogs/*
 * @var string $active      about|resume|portfolio|blog|contact
 * @var string $title       <title>
 * @var string $description meta description
 */
$bodyClass  = $bodyClass ?? '';
$scrollLine = $scrollLine ?? false;
$extraHead  = $extraHead ?? '';
$description = $description !== '' ? $description : setting('site_description');
$p = $prefix;
$nav = [
    'about'     => ['index', setting('nav_about')],
    'resume'    => ['resume', setting('nav_resume')],
    'portfolio' => ['portfolio', setting('nav_portfolio')],
    'blog'      => ['blog', setting('nav_blog')],
    'contact'   => ['contact', setting('nav_contact')],
];
$socialImage = absolute_url('assets/images/social.png');
$ogType = $ogType ?? 'website';
$socials = rows('SELECT icon, url FROM socials ORDER BY sort, id');
?>
<!DOCTYPE html>
<html lang="fa"<?= !empty($htmlDir) ? ' dir="rtl"' : '' ?>>

<head>
    <meta charset="utf-8" />
    <title><?= e($title) ?></title>

    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, viewport-fit=cover">
    <meta name="format-detection" content="telephone=no" />
    <meta name="format-detection" content="address=no" />
    <meta name="author" content="<?= s('site_author') ?>" />
    <meta name="description" content="<?= e($description) ?>" />

    <meta name="theme-color" content="#121212">
    <meta name="color-scheme" content="dark">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($title) ?>">
    <meta name="twitter:description" content="<?= e($description) ?>">
    <meta name="twitter:image" content="<?= e($socialImage) ?>">

    <meta property="og:title" content="<?= e($title) ?>" />
    <meta property="og:type" content="<?= e($ogType) ?>" />
    <meta property="og:locale" content="fa_IR" />
    <meta property="og:image" content="<?= e($socialImage) ?>" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:description" content="<?= e($description) ?>" />
    <meta property="og:site_name" content="<?= s('site_author') ?>" />

    <link rel="icon" href="<?= $p ?>assets/images/favicons/favicon.svg" type="image/svg+xml">
    <link rel="icon" href="<?= $p ?>assets/images/favicons/favicon-32x32.png" sizes="32x32" type="image/png">
    <link rel="icon" href="<?= $p ?>assets/images/favicons/favicon-16x16.png" sizes="16x16" type="image/png">
    <link rel="shortcut icon" href="<?= $p ?>assets/images/favicons/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= $p ?>assets/images/favicons/apple-touch-icon-180x180.png">
    <link rel="apple-touch-icon" sizes="152x152" href="<?= $p ?>assets/images/favicons/apple-touch-icon-152x152.png">
    <link rel="manifest" href="<?= $p ?>assets/images/favicons/site.webmanifest">

    <script>
        (function (d) {
            var r = d.documentElement;
            r.classList.add('pf-js', 'pf-loading');
            try { if (sessionStorage.getItem('pf_seen')) { r.classList.add('pf-repeat'); } } catch (e) {}
            // If the page script never finishes, uncover the page instead of hiding it forever.
            setTimeout(function () { if (!r.classList.contains('pf-ready')) { r.classList.remove('pf-js', 'pf-loading'); } }, 9000);
        })(document);
    </script>

    <link rel="preload" href="<?= $p ?>assets/fonts/IRY-Light.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?= $p ?>assets/fonts/IRY-ExtraBold.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?= $p ?>assets/fonts/Sarb-Regular.woff2" as="font" type="font/woff2" crossorigin>

    <link rel="stylesheet" type="text/css" href="<?= $p ?>assets/styles/vendors/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="<?= $p ?>assets/styles/style.css?v=<?= ASSET_VERSION ?>" />
    <link rel="stylesheet" type="text/css" href="<?= $p ?>assets/styles/rtl.css?v=<?= ASSET_VERSION ?>" />
    <link rel="stylesheet" type="text/css" href="<?= $p ?>assets/styles/enhance.css?v=<?= ASSET_VERSION ?>" />
<?= $extraHead ?>
</head>

<body<?= $bodyClass !== '' ? ' class="' . e($bodyClass) . '"' : '' ?>>
    <div class="preloader" id="preloader" role="presentation" aria-hidden="true">
        <div class="preloader__panel preloader__panel--top"></div>
        <div class="preloader__panel preloader__panel--bottom"></div>
        <div class="preloader__core">
            <div class="preloader__logo">
                <svg class="preloader__ring" viewBox="0 0 120 120" focusable="false">
                    <defs>
                        <linearGradient id="pf-ring" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#FFDB6E"/><stop offset="1" stop-color="#FFBC5E"/></linearGradient>
                    </defs>
                    <circle class="ring-track" cx="60" cy="60" r="54"/>
                    <circle class="ring-fill" cx="60" cy="60" r="54"/>
                </svg>
                <svg class="preloader__mark" viewBox="0 0 512 512" focusable="false">
                    <defs>
                        <linearGradient id="pf-mark" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#FFDB6E"/><stop offset="1" stop-color="#FFBC5E"/></linearGradient>
                    </defs>
                    <path pathLength="1" d="M132 380V150L256 296L380 150V380"/>
                </svg>
            </div>
            <div class="preloader__name"><?= s('profile_name') ?></div>
            <div class="preloader__role"><?= s('profile_badge') ?></div>
            <div class="preloader__bar"><span></span></div>
            <div class="preloader__percent">۰٪</div>
        </div>
    </div>
<?php if ($scrollLine): ?>
    <div class="scroll-line"></div>
<?php endif; ?>
    <main class="main">
        <div class="container gutter-top gutter-bottom">
            <div class="row sticky-parent">
                <aside class="col-12 col-md-12 col-xl-3">
                    <div class="sidebar box-outer sticky-column">
                        <div class="sidebar__base-info">
                            <figure class="avatar-box">
                                <img src="<?= asset(setting('profile_avatar'), $p) ?>" alt="<?= s('profile_name') ?>">
                            </figure>

                            <div class="text-xl-center">
                                <h3 class="title title--h3 sidebar__name"><?= s('profile_name') ?></h3>
                                <div class="badge"><?= s('profile_badge') ?></div>
                            </div>

                            <button class="btn btn--small btn--icon-right sidebar__btn js-btn-toggle">
                                <span><?= s('sidebar_toggle_text') ?></span>
                                <i class="feathericon-chevron-down"></i>
                            </button>
                        </div>

                        <div class="sidebar__additional-info js-show">
                            <div class="separation"></div>
                            <ul class="details-info">
                                <li class="details-info__item">
                                    <span class="box icon-box"><i class="font-icon icon-envelope"></i></span>
                                    <div class="contacts-block__info">
                                        <span class="overhead">ایمیل</span>
                                        <a class="text-overflow myemail" href="mailto:<?= s('contact_email') ?>"
                                            title="<?= s('contact_email') ?>"><?= s('contact_email') ?></a>
                                    </div>
                                </li>

                                <li class="details-info__item">
                                    <span class="box icon-box"><i class="font-icon icon-phone"></i></span>
                                    <div class="contacts-block__info">
                                        <span class="overhead">تلفن</span>
                                        <span class="text-overflow" title="<?= s('contact_phone') ?>"><?= s('contact_phone') ?></span>
                                    </div>
                                </li>

                                <li class="details-info__item">
                                    <span class="box icon-box"><i class="font-icon icon-calendar"></i></span>
                                    <div class="contacts-block__info">
                                        <span class="overhead">وضعیت همکاری</span>
                                        <span class="text-overflow" title="<?= s('work_status') ?>"><?= s('work_status') ?></span>
                                    </div>
                                </li>

                                <li class="details-info__item">
                                    <span class="box icon-box"><i class="font-icon icon-location"></i></span>
                                    <div class="contacts-block__info">
                                        <span class="overhead">موقعیت مکانی</span>
                                        <span class="text-overflow" title="<?= s('location') ?>"><?= s('location') ?></span>
                                    </div>
                                </li>
                            </ul>

                            <div class="separation d-xl-none"></div>

                            <div class="social">
<?php foreach ($socials as $social): ?>
                                <a class="social__link" href="<?= e($social['url']) ?>">
                                    <i class="<?= e($social['icon']) ?>"></i>
                                </a>
<?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </aside>

                <div class="col-12 col-md-12 col-xl-9">
                    <div class="box-outer<?= !empty($boxOuterClass) ? ' ' . e($boxOuterClass) : '' ?>">
                        <div class="nav-container">
                            <ul class="nav">
<?php foreach ($nav as $key => [$href, $label]): ?>
                                <li class="nav__item"><a <?= $key === $active ? 'class="active" ' : '' ?>href="<?= $p . $href ?>"><?= e($label) ?></a></li>
<?php endforeach; ?>
                            </ul>
                        </div>
