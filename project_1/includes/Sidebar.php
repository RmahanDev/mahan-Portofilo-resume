<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentPage = $currentPage ?? basename($_SERVER['PHP_SELF']);
$adminUsername = $adminUsername ?? ($_SESSION['admin_username'] ?? 'Admin');
?>

<aside
        id="sidebar">
	<div class="sidebar-header">
		<h4>PORTFOLIO<span>.</span></h4>
		<small class="text-muted">پنل مدیریت محتوا</small>
	</div>

	<div class="nav flex-column mt-3">
		<a href="EditIndex.php" class="nav-link <?= ($currentPage == 'EditIndex.php') ? 'active' : '' ?>">
			<i class="bi bi-house-door"></i> صفحه اصلی
		</a>

		<a href="AddBlog.php" class="nav-link <?= ($currentPage == 'AddBlog.php') ? 'active' : '' ?>">
			<i class="bi bi-journal-text"></i> مدیریت بلاگ
		</a>

		<a href="AddPortofolio.php" class="nav-link <?= ($currentPage == 'AddPortofolio.php') ? 'active' : '' ?>">
			<i class="bi bi-briefcase"></i> پورتفولیو
		</a>

		<a href="AddResume.php" class="nav-link <?= ($currentPage == 'AddResume.php') ? 'active' : '' ?>">
			<i class="bi bi-file-earmark-person"></i> رزومه و سوابق
		</a>

		<a href="VerfiyComments.php" class="nav-link <?= ($currentPage == 'VerfiyComments.php') ? 'active' : '' ?>">
			<i class="bi bi-chat-left-dots"></i> تایید نظرات
		</a>

		<a href="EditContact.php" class="nav-link <?= ($currentPage == 'EditContact.php') ? 'active' : '' ?>">
			<i class="bi bi-envelope-at"></i> اطلاعات تماس
		</a>

		<hr class="mx-3 my-4" style="border-color: var(--border-color);">

		<a href="Settings.php" class="nav-link <?= ($currentPage == 'Settings.php') ? 'active' : '' ?>">
			<i class="bi bi-gear"></i> تنظیمات پنل
		</a>

		<a href="login/logout.php" class="nav-link text-danger-emphasis">
			<i class="bi bi-box-arrow-right text-danger"></i> خروج از حساب
		</a>
	</div>
</aside>

<!-- شروع محتوای اصلی -->
<main id="main-content" class="w-100">
	<div class="topbar justify-content-between">
		<div class="page-title">
			<h5 class="mb-0 fw-bold">داشبورد مدیریتی</h5>
		</div>

		<div class="d-flex align-items-center gap-3">
			<div class="admin-profile">
				<i class="bi bi-person-circle gold-text"></i>
				<span class="small fw-bold"><?= htmlspecialchars($adminUsername) ?></span>
			</div>
			<a href="login/logout.php" class="btn btn-sm btn-outline-danger d-none d-md-block">
				<i class="bi bi-power"></i>
			</a>
		</div>
	</div>

	<div class="content-padding">
<?php
require_once __DIR__ . '/footer.php';
?>