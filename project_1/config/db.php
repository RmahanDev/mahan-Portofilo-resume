<?php
// config/db.php

declare(strict_types=1);

$host = 'localhost';
$dbname = 'PortfolioDB';
$username = 'root';      // این‌ها را طبق محیط خودت تنظیم کن
$password = '';          // مثلاً در لوکال XAMPP خالی است

try {
	$pdo = new PDO(
		"mysql:host=$host;dbname=$dbname;charset=utf8mb4",
		$username,
		$password,
		[
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::ATTR_EMULATE_PREPARES => false,
		]
	);
} catch (PDOException $e) {
	// در محیط واقعی بهتر است پیام خطا را لاگ کنی نه نمایش مستقیم
	die("Connection failed: " . $e->getMessage());
}
