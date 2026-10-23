<?php
/**
 * Admin layout: same skeleton, sidebar and classes as the public site so the panel looks like it.
 *
 * @var string $pageTitle
 * @var string $menu      key of the active sidebar item
 */
$_unread = unread_count();
$menu = $menu ?? '';
$_menuItems = [
    ['dashboard', 'index.php', 'feathericon-grid', 'داشبورد'],
    ['messages', 'messages.php', 'feathericon-inbox', 'پیام‌ها'],
    ['projects', 'list.php?r=projects', 'feathericon-briefcase', 'نمونه کارها'],
    ['posts', 'list.php?r=posts', 'feathericon-edit', 'پست‌های وبلاگ'],
    ['skills', 'skills.php', 'feathericon-bar-chart-2', 'مهارت‌ها و درصدها'],
];
foreach (setting_groups() as $_gk => $_group) {
    $_menuItems[] = ['settings-' . $_gk, 'settings.php?g=' . $_gk, $_group['icon'], $_group['title']];
}
$_menuItems[] = ['account', 'account.php', 'feathericon-lock', 'حساب کاربری'];
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="utf-8" />
    <title><?= e($pageTitle) ?> | پنل مدیریت</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="robots" content="noindex, nofollow">
    <link rel="shortcut icon" href="../assets/images/favicons/favicon.png" type="image/png">
    <link rel="stylesheet" type="text/css" href="../assets/styles/vendors/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="../assets/styles/style.css" />
    <link rel="stylesheet" type="text/css" href="../assets/styles/rtl.css" />
    <link rel="stylesheet" type="text/css" href="assets/admin.css?v=1" />
</head>

<body class="admin">
    <main class="main">
        <div class="container gutter-top gutter-bottom">
            <div class="row sticky-parent">
                <aside class="col-12 col-md-12 col-xl-3">
                    <div class="sidebar box-outer sticky-column">
                        <div class="sidebar__base-info">
                            <figure class="avatar-box">
                                <img src="<?= asset(setting('profile_avatar'), '../') ?>" alt="<?= s('profile_name') ?>">
                            </figure>

                            <div class="text-xl-center">
                                <h3 class="title title--h3 sidebar__name"><?= s('profile_name') ?></h3>
                                <div class="badge">پنل مدیریت</div>
                            </div>

                            <button class="btn btn--small btn--icon-right sidebar__btn js-btn-toggle">
                                <span>منوی مدیریت</span>
                                <i class="feathericon-chevron-down"></i>
                            </button>
                        </div>

                        <div class="sidebar__additional-info js-show">
                            <div class="separation"></div>
                            <ul class="admin-menu">
<?php foreach ($_menuItems as [$_key, $_href, $_icon, $_label]): ?>
                                <li>
                                    <a class="admin-menu__link<?= $_key === $menu ? ' is-active' : '' ?>" href="<?= e($_href) ?>">
                                        <span class="box icon-box"><i class="font-icon <?= e($_icon) ?>"></i></span>
                                        <span class="admin-menu__text"><?= e($_label) ?></span>
<?php if ($_key === 'messages' && $_unread > 0): ?>
                                        <span class="admin-count"><?= $_unread ?></span>
<?php endif; ?>
                                    </a>
                                </li>
<?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </aside>

                <div class="col-12 col-md-12 col-xl-9">
                    <div class="box-outer">
                        <div class="nav-container">
                            <ul class="nav">
                                <li class="nav__item"><a href="../index" target="_blank">مشاهده سایت <i class="feathericon-external-link"></i></a></li>
                                <li class="nav__item"><a href="messages.php">پیام‌ها<?= $_unread ? ' (' . $_unread . ')' : '' ?></a></li>
                                <li class="nav__item"><a href="logout.php">خروج <i class="feathericon-log-out"></i></a></li>
                            </ul>
                        </div>

                        <div class="pb-3">
                            <h1 class="title title--h1 title__separate"><?= e($pageTitle) ?></h1>
                        </div>
<?php foreach (take_flashes() as [$_type, $_message]): ?>
                        <div class="admin-alert admin-alert--<?= e($_type) ?>"><?= e($_message) ?></div>
<?php endforeach; ?>
