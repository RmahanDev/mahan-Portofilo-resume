<?php
// dashboard/login/register.php
session_start();
require_once __DIR__ . '/../../config/db.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$username = trim($_POST['username'] ?? '');
	$password = trim($_POST['password'] ?? '');
	$confirm  = trim($_POST['confirm_password'] ?? '');
	
	// اعتبارسنجی ساده
	if ($username === '') {
		$errors[] = 'نام کاربری الزامی است.';
	}
	if ($password === '') {
		$errors[] = 'رمز عبور الزامی است.';
	}
	if ($password !== $confirm) {
		$errors[] = 'تکرار رمز عبور با خود رمز یکی نیست.';
	}
	if (strlen($password) < 6) {
		$errors[] = 'رمز عبور باید حداقل ۶ کاراکتر باشد.';
	}
	
	if (empty($errors)) {
		// چک یکتا بودن یوزرنیم
		$stmt = $pdo->prepare("SELECT ID FROM UserAdmin WHERE Username = :username");
		$stmt->execute(['username' => $username]);
		if ($stmt->fetch()) {
			$errors[] = 'این نام کاربری قبلاً ثبت شده است.';
		} else {
			// ذخیره ادمین جدید
			$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
			
			$insert = $pdo->prepare(
				"INSERT INTO UserAdmin (Username, Password) VALUES (:username, :password)"
			);
			$insert->execute([
				'username' => $username,
				'password' => $hashedPassword
			]);
			
			$success = 'ادمین با موفقیت ثبت شد. اکنون می‌توانید وارد شوید.';
		}
	}
}
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
	<meta charset="utf-8">
	<title>ثبت‌نام ادمین داشبورد</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- Bootstrap 5 CSS CDN -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
	      rel="stylesheet"
	      integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
	      crossorigin="anonymous">
	<style>
        body {
            background: #f3f4f6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
        }
        .auth-card {
            max-width: 420px;
            width: 100%;
        }
	</style>
</head>
<body>

<div class="auth-card">
	<div class="card shadow-sm border-0">
		<div class="card-body p-4">
			<div class="text-center mb-4">
				<h1 class="h4 fw-bold">ثبت‌نام ادمین</h1>
				<p class="text-muted mb-0">ایجاد حساب برای مدیریت داشبورد</p>
			</div>
			
			<?php if (!empty($errors)): ?>
				<div class="alert alert-danger">
					<ul class="mb-0">
						<?php foreach ($errors as $error): ?>
							<li><?= htmlspecialchars($error) ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
			
			<?php if ($success !== ''): ?>
				<div class="alert alert-success">
					<?= htmlspecialchars($success) ?>
				</div>
			<?php endif; ?>
			
			<form method="post" novalidate>
				<div class="mb-3">
					<label for="username" class="form-label">نام کاربری</label>
					<input type="text"
					       class="form-control"
					       id="username"
					       name="username"
					       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
					       required>
				</div>
				
				<div class="mb-3">
					<label for="password" class="form-label">رمز عبور</label>
					<input type="password"
					       class="form-control"
					       id="password"
					       name="password"
					       required>
				</div>
				
				<div class="mb-3">
					<label for="confirm_password" class="form-label">تکرار رمز عبور</label>
					<input type="password"
					       class="form-control"
					       id="confirm_password"
					       name="confirm_password"
					       required>
				</div>
				
				<button type="submit" class="btn btn-primary w-100">
					ثبت‌نام
				</button>
				
				<div class="mt-3 text-center">
					<small>حساب دارید؟ <a href="login.php">ورود به داشبورد</a></small>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Bootstrap 5 JS CDN -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wv6p6R/+qzK5S2e5kWFjvGJQ5jR9a7Ra9rJ"
        crossorigin="anonymous"></script>
</body>
</html>
