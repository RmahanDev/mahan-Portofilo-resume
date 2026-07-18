<?php
// EditIndex.php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

if (empty($_SESSION['admin_id'])) {
	header('Location: login/login.php');
	exit;
}

require_once __DIR__ . '/../config/db.php';

// شناسه کاربر جاری (ادمین)
$userId = $_SESSION['admin_id'];

// گرفتن پروفایل
$stmtProfile = $pdo->prepare("SELECT * FROM Profile WHERE UserID = :uid LIMIT 1");
$stmtProfile->execute([':uid' => $userId]);
$profile = $stmtProfile->fetch(PDO::FETCH_ASSOC);

// اگر پروفایل وجود نداشت، می‌توانیم بعداً اینجا Insert اولیه انجام دهیم
if (!$profile) {
	$profile = [
		'ID' => null,
		'Pname' => '',
		'Pposition' => '',
		'Pemail' => '',
		'Pphonenumber' => '',
		'Pcooperationstatus' => 0,
		'location' => '',
		'Pprofilepic' => ''
	];
}

// گرفتن درباره من
$aboutMe = null;
if ($profile['ID']) {
	$stmtAbout = $pdo->prepare("SELECT * FROM AboutMe WHERE ProfileID = :pid LIMIT 1");
	$stmtAbout->execute([':pid' => $profile['ID']]);
	$aboutMe = $stmtAbout->fetch(PDO::FETCH_ASSOC);
}
if (!$aboutMe) {
	$aboutMe = [
		'ID' => null,
		'aboutme' => '',
		'skills' => ''
	];
}

// گرفتن کارت‌های درباره من
$aboutMeCards = [];
if ($aboutMe['ID']) {
	$stmtCards = $pdo->prepare("SELECT * FROM AboutMeCards WHERE AboutMeID = :aid ORDER BY ID DESC");
	$stmtCards->execute([':aid' => $aboutMe['ID']]);
	$aboutMeCards = $stmtCards->fetchAll(PDO::FETCH_ASSOC);
}

// برای هایلایت منوی سایدبار
$currentPage = basename($_SERVER['PHP_SELF']);

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<!-- محتوای صفحه EditIndex -->
<div class="admin-card mb-4">
	<h2 class="gold-text mb-3">ویرایش پروفایل</h2>
	<form action="handlers/profile_update.php" method="post" enctype="multipart/form-data" class="row g-3">
		<input type="hidden" name="profile_id" value="<?= htmlspecialchars($profile['ID'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

		<div class="col-md-6">
			<label class="form-label">نام و نام خانوادگی</label>
			<input type="text" name="Pname" class="form-control bg-dark text-light border-secondary"
			       value="<?= htmlspecialchars($profile['Pname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
		</div>

		<div class="col-md-6">
			<label class="form-label">عنوان شغلی / پوزیشن</label>
			<input type="text" name="Pposition" class="form-control bg-dark text-light border-secondary"
			       value="<?= htmlspecialchars($profile['Pposition'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
		</div>

		<div class="col-md-6">
			<label class="form-label">ایمیل</label>
			<input type="email" name="Pemail" class="form-control bg-dark text-light border-secondary"
			       value="<?= htmlspecialchars($profile['Pemail'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
		</div>

		<div class="col-md-6">
			<label class="form-label">شماره تماس</label>
			<input type="text" name="Pphonenumber" class="form-control bg-dark text-light border-secondary"
			       value="<?= htmlspecialchars($profile['Pphonenumber'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
		</div>

		<div class="col-md-6">
			<label class="form-label">وضعیت همکاری</label>
			<select name="Pcooperationstatus" class="form-select bg-dark text-light border-secondary">
				<option value="1" <?= (!empty($profile['Pcooperationstatus'])) ? 'selected' : ''; ?>>در دسترس برای همکاری</option>
				<option value="0" <?= (empty($profile['Pcooperationstatus'])) ? 'selected' : ''; ?>>عدم پذیرش همکاری</option>
			</select>
		</div>

		<div class="col-md-6">
			<label class="form-label">موقعیت / شهر</label>
			<input type="text" name="location" class="form-control bg-dark text-light border-secondary"
			       value="<?= htmlspecialchars($profile['location'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
		</div>

		<div class="col-md-6">
			<label class="form-label">تصویر پروفایل</label>
			<input type="file" name="Pprofilepic" class="form-control bg-dark text-light border-secondary">
			<?php if (!empty($profile['Pprofilepic'])): ?>
				<small class="text-muted d-block mt-2">
					تصویر فعلی:
					<img src="<?= htmlspecialchars($profile['Pprofilepic'], ENT_QUOTES, 'UTF-8'); ?>"
					     alt="Profile Pic" style="height: 40px; border-radius: 50%; margin-right: 10px;">
				</small>
			<?php endif; ?>
		</div>

		<div class="col-12 text-end mt-3">
			<button type="submit" class="btn btn-outline-warning px-4">
				بروزرسانی پروفایل
			</button>
		</div>
	</form>
</div>

<!-- درباره من + ادیتور پیشرفته -->
<div class="admin-card mb-4">
	<h2 class="gold-text mb-3">درباره من و مهارت‌ها</h2>
	<form action="handlers/aboutme_update.php" method="post">
		<input type="hidden" name="aboutme_id" value="<?= htmlspecialchars($aboutMe['ID'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
		<input type="hidden" name="profile_id" value="<?= htmlspecialchars($profile['ID'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

		<div class="mb-3">
			<label class="form-label">متن درباره من (پشتیبانی کامل HTML)</label>
			<textarea id="aboutme_editor" name="aboutme" class="form-control" rows="8">
<?= htmlspecialchars($aboutMe['aboutme'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
            </textarea>
		</div>

		<div class="mb-3">
			<label class="form-label">مهارت‌ها (می‌تواند به صورت HTML / لیست باشد)</label>
			<textarea id="skills_editor" name="skills" class="form-control" rows="5">
<?= htmlspecialchars($aboutMe['skills'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
            </textarea>
		</div>

		<div class="text-end">
			<button type="submit" class="btn btn-outline-warning px-4">
				ذخیره درباره من
			</button>
		</div>
	</form>
</div>

<!-- کارت‌های درباره من -->
<div class="admin-card mb-4">
	<div class="d-flex justify-content-between align-items-center mb-3">
		<h2 class="gold-text mb-0">کارت‌های درباره من</h2>
		<button type="button" class="btn btn-sm btn-outline-warning"
		        data-bs-toggle="modal" data-bs-target="#cardAddModal">
			<i class="bi bi-plus-lg"></i> افزودن کارت جدید
		</button>
	</div>

	<div class="table-responsive">
		<table class="table table-dark table-hover align-middle mb-0">
			<thead>
			<tr>
				<th style="width: 60px;">#</th>
				<th>عنوان</th>
				<th>متن</th>
				<th style="width: 180px;">عملیات</th>
			</tr>
			</thead>
			<tbody>
			<?php if (!empty($aboutMeCards)): ?>
				<?php foreach ($aboutMeCards as $index => $card): ?>
					<tr>
						<td><?= $index + 1; ?></td>
						<td><?= htmlspecialchars($card['sub'], ENT_QUOTES, 'UTF-8'); ?></td>
						<td>
                            <span class="text-muted">
                                <?= htmlspecialchars(mb_strimwidth($card['text'], 0, 80, '...', 'UTF-8'), ENT_QUOTES, 'UTF-8'); ?>
                            </span>
						</td>
						<td>
							<div class="btn-group btn-group-sm" role="group">
								<!-- نمایش -->
								<button type="button" class="btn btn-outline-light"
								        data-bs-toggle="modal"
								        data-bs-target="#cardViewModal<?= $card['ID']; ?>">
									نمایش
								</button>
								<!-- ویرایش -->
								<button type="button" class="btn btn-outline-warning"
								        data-bs-toggle="modal"
								        data-bs-target="#cardEditModal<?= $card['ID']; ?>">
									ویرایش
								</button>
								<!-- حذف -->
								<form action="handlers/card_delete.php" method="post" class="d-inline">
									<input type="hidden" name="card_id"
									       value="<?= htmlspecialchars($card['ID'], ENT_QUOTES, 'UTF-8'); ?>">
									<button type="submit" class="btn btn-outline-danger"
									        onclick="return confirm('آیا از حذف این کارت مطمئن هستید؟');">
										حذف
									</button>
								</form>
							</div>
						</td>
					</tr>

					<!-- Modal نمایش کارت -->
					<div class="modal fade" id="cardViewModal<?= $card['ID']; ?>" tabindex="-1"
					     aria-hidden="true">
						<div class="modal-dialog modal-lg modal-dialog-centered">
							<div class="modal-content bg-dark text-light border-secondary">
								<div class="modal-header border-secondary">
									<h5 class="modal-title gold-text">
										<?= htmlspecialchars($card['sub'], ENT_QUOTES, 'UTF-8'); ?>
									</h5>
									<button type="button" class="btn-close btn-close-white"
									        data-bs-dismiss="modal" aria-label="Close"></button>
								</div>
								<div class="modal-body">
									<p><?= nl2br(htmlspecialchars($card['text'], ENT_QUOTES, 'UTF-8')); ?></p>
								</div>
							</div>
						</div>
					</div>

					<!-- Modal ویرایش کارت -->
					<div class="modal fade" id="cardEditModal<?= $card['ID']; ?>" tabindex="-1"
					     aria-hidden="true">
						<div class="modal-dialog modal-lg modal-dialog-centered">
							<div class="modal-content bg-dark text-light border-secondary">
								<div class="modal-header border-secondary">
									<h5 class="modal-title gold-text">ویرایش کارت</h5>
									<button type="button" class="btn-close btn-close-white"
									        data-bs-dismiss="modal" aria-label="Close"></button>
								</div>
								<form action="handlers/card_update.php" method="post">
									<div class="modal-body">
										<input type="hidden" name="card_id"
										       value="<?= htmlspecialchars($card['ID'], ENT_QUOTES, 'UTF-8'); ?>">
										<div class="mb-3">
											<label class="form-label">عنوان کارت</label>
											<input type="text" name="sub" class="form-control bg-dark text-light border-secondary"
											       value="<?= htmlspecialchars($card['sub'], ENT_QUOTES, 'UTF-8'); ?>">
										</div>
										<div class="mb-3">
											<label class="form-label">متن کارت</label>
											<textarea name="text" rows="4"
											          class="form-control bg-dark text-light border-secondary"><?= htmlspecialchars($card['text'], ENT_QUOTES, 'UTF-8'); ?></textarea>
										</div>
									</div>
									<div class="modal-footer border-secondary">
										<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بستن</button>
										<button type="submit" class="btn btn-outline-warning">ذخیره تغییرات</button>
									</div>
								</form>
							</div>
						</div>
					</div>

				<?php endforeach; ?>
			<?php else: ?>
				<tr>
					<td colspan="4" class="text-center text-muted">
						هیچ کارتی برای درباره من ثبت نشده است.
					</td>
				</tr>
			<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>

<!-- Modal افزودن کارت جدید -->
<div class="modal fade" id="cardAddModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered">
		<div class="modal-content bg-dark text-light border-secondary">
			<div class="modal-header border-secondary">
				<h5 class="modal-title gold-text">افزودن کارت جدید</h5>
				<button type="button" class="btn-close btn-close-white"
				        data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<form action="handlers/card_add.php" method="post">
				<div class="modal-body">
					<input type="hidden" name="aboutme_id"
					       value="<?= htmlspecialchars($aboutMe['ID'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
					<div class="mb-3">
						<label class="form-label">عنوان کارت</label>
						<input type="text" name="sub" class="form-control bg-dark text-light border-secondary" required>
					</div>
					<div class="mb-3">
						<label class="form-label">متن کارت</label>
						<textarea name="text" rows="4" class="form-control bg-dark text-light border-secondary" required></textarea>
					</div>
				</div>
				<div class="modal-footer border-secondary">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
					<button type="submit" class="btn btn-outline-warning">ثبت کارت</button>
				</div>
			</form>
		</div>
	</div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>

<!-- CKEditor 5 از CDN -->
<script src="https://cdn.ckeditor.com/ckeditor5/41.0.0/classic/ckeditor.js"></script>
<script>
    ClassicEditor
        .create(document.querySelector('#aboutme_editor'), {
            toolbar: [
                'heading', '|',
                'bold', 'italic', 'underline', 'link',
                'bulletedList', 'numberedList',
                'blockQuote', 'insertTable', 'imageUpload',
                'undo', 'redo'
            ]
        })
        .catch(error => console.error(error));

    ClassicEditor
        .create(document.querySelector('#skills_editor'), {
            toolbar: [
                'bold', 'italic', 'underline', 'link',
                'bulletedList', 'numberedList',
                'undo', 'redo'
            ]
        })
        .catch(error => console.error(error));
</script>
