<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (empty($_SESSION['admin_id'])) {
    header('Location: /login/login.php');
    exit;
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/sidebar.php';

$adminUsername = $_SESSION['admin_username'] ?? 'Admin';
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>مدیریت پورتفولیو | <?= $adminUsername ?></title>

    <!-- Bootstrap 5 RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/dist/css/bootstrap.rtl.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --sidebar-width: 260px;
            --gold: #d4af37;
            --gold-bright: #f5d76e;
            --dark-bg: #0b0b0b;
            --card-bg: #141414;
            --border-color: rgba(212, 175, 55, 0.15);
        }

        body {
            background-color: var(--dark-bg);
            color: #e0e0e0;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            overflow-x: hidden;
        }

        .admin-profile {
            display: flex;
            align-items: center;
            gap: 8px;
        }


        /* حذف خط زیر تمام لینک‌های سایدبار */
        #sidebar .nav-link {
            text-decoration: none !important; /* این خط را حتما اضافه کن */
            color: #aaa !important;
            padding: 12px 20px;
            margin: 4px 12px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: 0.3s;
        }

        /* برای اطمینان از اینکه در حالت Hover هم خط زیر ظاهر نمی‌شود */
        #sidebar .nav-link:hover {
            text-decoration: none !important;
            background: rgba(212, 175, 55, 0.05);
            color: var(--gold-bright) !important;
        }

        .topbar {
            height: 70px;
            background: rgba(15, 15, 15, 0.9);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .admin-profile {
            background: rgba(212, 175, 55, 0.1);
            padding: 6px 15px;
            border-radius: 50px;
            border: 1px solid var(--border-color);
        }

        /* Sidebar Styling */
        #sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            right: 0;
            top: 0;
            background: linear-gradient(180deg, #0f0f0f 0%, #050505 100%);
            border-left: 1px solid var(--border-color);
            z-index: 1000;
            transition: all 0.3s;
        }

        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 10px;
        }

        .sidebar-header h4 {
            color: var(--gold);
            font-weight: 800;
            letter-spacing: 1px;
            margin: 0;
        }

        .nav-link {
            color: #aaa !important;
            padding: 12px 20px;
            margin: 4px 12px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: 0.3s;
        }

        .nav-link i { font-size: 1.1rem; }

        .nav-link:hover {
            background: rgba(212, 175, 55, 0.05);
            color: var(--gold-bright) !important;
        }

        .nav-link.active {
            background: linear-gradient(90deg, rgba(212, 175, 55, 0.2) 0%, transparent 100%);
            color: var(--gold) !important;
            border-right: 3px solid var(--gold);
        }

        /* Main Content Area */
        #main-content {
            margin-right: var(--sidebar-width);
            min-height: 100vh;
            transition: all 0.3s;
        }

        /* Topbar Styling */
        .topbar {
            height: 70px;
            background: rgba(11, 11, 11, 0.8);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            padding: 0 30px;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .admin-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255,255,255,0.03);
            padding: 5px 15px;
            border-radius: 50px;
            border: 1px solid var(--border-color);
        }

        .gold-text { color: var(--gold); }

        .content-padding { padding: 30px; }

        /* Custom Card for Content */
        .admin-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            padding: 25px;
        }
    </style>
</head>
<body>

<div class="d-flex">
