<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: ../EditIndex.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $errors[] = 'نام کاربری و رمز عبور را وارد کنید.';
    } else {
        $stmt = $pdo->prepare('SELECT ID, Username, Password FROM UserAdmin WHERE Username = :username LIMIT 1');
        $stmt->execute(['username' => $username]);
        $admin = $stmt->fetch();

        if (!$admin || !password_verify($password, $admin['Password'])) {
            $errors[] = 'نام کاربری یا رمز عبور نادرست است.';
        } else {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['ID'];
            $_SESSION['admin_username'] = $admin['Username'];

            header('Location: ../EditIndex.php');
            exit;
        }
    }
}
?>

<!doctype html>
<html lang="fa" dir="rtl">
<head>
	<meta charset="utf-8">
	<title>ورود به داشبورد ادمین</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- Bootstrap 5 CSS CDN -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
	      rel="stylesheet"
	      integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
	      crossorigin="anonymous">
	<style>
        body {
            background: radial-gradient(circle at top left, #1d4ed8, #0f172a);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
        }
        .auth-card {
            max-width: 400px;
            width: 100%;
        }
        .card {
            border-radius: 1rem;
        }
	</style>
</head>
<body>

<div class="auth-card">
	<div class="card shadow-lg border-0">
		<div class="card-body p-4">
			<div class="text-center mb-4">
				<h1 class="h4 fw-bold">ورود ادمین</h1>
				<p class="text-muted mb-0">برای مدیریت محتوای پرتفولیو وارد شوید</p>
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
				
				<div class="d-flex justify-content-between align-items-center mb-3">
					<div class="form-check">
						<input class="form-check-input" type="checkbox" value="" id="rememberMe">
						<label class="form-check-label" for="rememberMe">
							مرا به خاطر بسپار
						</label>
					</div>
					<!-- اگر صفحه فراموشی رمز داشتی، لینک قرار بده -->
					<!-- <a href="#" class="small">فراموشی رمز عبور؟</a> -->
				</div>
				
				<button type="submit" class="btn btn-primary w-100">
					ورود به داشبورد
				</button>
				
				<div class="mt-3 text-center">
					<small>ادمین جدید هستید؟ <a href="register.php">ایجاد حساب ادمین</a></small>
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
