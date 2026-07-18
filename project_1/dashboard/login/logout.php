<?php

session_start();

// پاک کردن همه متغیرهای سشن
$_SESSION = [];

// اگر کوکی سشن هم وجود دارد، حذف شود
if (ini_get("session.use_cookies")) {
	$params = session_get_cookie_params();
	setcookie(
		session_name(),
		'',
		time() - 3600,
		$params["path"],
		$params["domain"],
		$params["secure"],
		$params["httponly"]
	);
}

// نابود کردن سشن
session_destroy();

// برگشت به صفحه لاگین
header("Location: login.php");
exit;
