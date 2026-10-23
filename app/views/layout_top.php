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
$socials = rows('SELECT icon, url FROM socials ORDER BY sort, id');
?>
<!DOCTYPE html>
<html lang="fa"<?= !empty($htmlDir) ? ' dir="rtl"' : '' ?>>

<head>
    <meta charset="utf-8" />
    <title><?= e($title) ?></title>

    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="format-detection" content="telephone=no" />
    <meta name="format-detection" content="address=no" />
    <meta name="author" content="<?= s('site_author') ?>" />
    <meta name="description" content="<?= e($description) ?>" />

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($title) ?>">
    <meta name="twitter:description" content="<?= e($description) ?>">
    <meta name="twitter:image" content="<?= $p ?>assets/images/social.html">

    <meta property="og:title" content="<?= e($title) ?>" />
    <meta property="og:type" content="website" />
    <meta property="og:image" content="<?= $p ?>assets/images/social.html" />
    <meta property="og:description" content="<?= e($description) ?>" />
    <meta property="og:site_name" content="<?= s('site_author') ?>" />

    <link rel="apple-touch-icon" sizes="144x144" href="<?= $p ?>assets/images/favicons/apple-touch-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="114x114" href="<?= $p ?>assets/images/favicons/apple-touch-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="72x72" href="<?= $p ?>assets/images/favicons/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="57x57" href="<?= $p ?>assets/images/favicons/apple-touch-icon-57x57.png">
    <link rel="shortcut icon" href="<?= $p ?>assets/images/favicons/favicon.png" type="image/png">

    <link rel="stylesheet" type="text/css" href="<?= $p ?>assets/styles/vendors/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="<?= $p ?>assets/styles/style.css" />
    <link rel="stylesheet" type="text/css" href="<?= $p ?>assets/styles/rtl.css" />
<?= $extraHead ?>
</head>

<body<?= $bodyClass !== '' ? ' class="' . e($bodyClass) . '"' : '' ?>>
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
