<?php
// EditIndex.php - Rewritten

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['admin_id'])) {
    header('Location: login/login.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

// ==================
// Load Base Data
// ==================
$userId = $_SESSION['admin_id'];

// Profile
$stmtProfile = $pdo->prepare("SELECT * FROM Profile WHERE UserID = :uid LIMIT 1");
$stmtProfile->execute([':uid' => $userId]);
$profile = $stmtProfile->fetch(PDO::FETCH_ASSOC);

if (!$profile) {
    $profile = [
            'ID'                => null,
            'Pname'             => '',
            'Pposition'         => '',
            'Pemail'            => '',
            'Pphonenumber'      => '',
            'Pcooperationstatus'=> 0,
            'location'          => '',
            'Pprofilepic'       => ''
    ];
}

// AboutMe
$aboutMe = null;
if (!empty($profile['ID'])) {
    $stmtAbout = $pdo->prepare("SELECT * FROM AboutMe WHERE ProfileID = :pid LIMIT 1");
    $stmtAbout->execute([':pid' => $profile['ID']]);
    $aboutMe = $stmtAbout->fetch(PDO::FETCH_ASSOC);
}
if (!$aboutMe) {
    $aboutMe = [
            'ID'      => null,
            'aboutme' => '',
            'skills'  => ''
    ];
}

// AboutMeCards (info cards)
$aboutMeCards = [];
if (!empty($aboutMe['ID'])) {
    $stmtCards = $pdo->prepare("SELECT * FROM AboutMeCards WHERE AboutMeID = :aid ORDER BY ID DESC");
    $stmtCards->execute([':aid' => $aboutMe['ID']]);
    $aboutMeCards = $stmtCards->fetchAll(PDO::FETCH_ASSOC);
}

// AboutMeExp (companies experiences)
$aboutMeExps = [];
if (!empty($aboutMe['ID'])) {
    $stmtExps = $pdo->prepare("SELECT * FROM AboutMeExp WHERE AboutMeID = :aid ORDER BY ID DESC");
    $stmtExps->execute([':aid' => $aboutMe['ID']]);
    $aboutMeExps = $stmtExps->fetchAll(PDO::FETCH_ASSOC);
}

// برای هایلایت منوی سایدبار
$currentPage = basename($_SERVER['PHP_SELF']);

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<!-- =========================
     Global Styles
========================= -->
<style>
    body {
        background: radial-gradient(circle at top, #1b1b1f 0, #050509 45%, #000 100%);
        color: #f8f9fa;
        font-family: "IRANSans", "Vazir", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    .page-wrapper {
        padding: 1.5rem;
    }

    .gold-text {
        color: #f5c451 !important;
    }

    .muted-text {
        color: #a0a0aa;
    }

    .link-gold {
        color: #f5c451;
        text-decoration: none;
    }
    .link-gold:hover {
        color: #ffd86b;
        text-decoration: underline;
    }

    .admin-card {
        background: linear-gradient(135deg, rgba(8, 8, 14, 0.97), rgba(16, 16, 26, 0.98));
        border-radius: 20px;
        padding: 1.6rem 1.8rem;
        border: 1px solid rgba(245, 196, 81, 0.28);
        box-shadow:
                0 24px 60px rgba(0, 0, 0, 0.85),
                0 0 0 1px rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        position: relative;
        overflow: hidden;
    }

    .admin-card::before {
        content: "";
        position: absolute;
        inset: -40%;
        background:
                radial-gradient(circle at 0 0, rgba(245, 196, 81, 0.1), transparent 55%),
                radial-gradient(circle at 100% 100%, rgba(100, 120, 255, 0.12), transparent 45%);
        opacity: 0.85;
        mix-blend-mode: soft-light;
        pointer-events: none;
        z-index: -1;
    }

    .admin-card-header {
        border-bottom: 1px solid rgba(245, 196, 81, 0.25);
        padding-bottom: 0.75rem;
        margin-bottom: 1rem;
    }

    .admin-card-title {
        font-size: 1.2rem;
        font-weight: 600;
        letter-spacing: 0.01em;
    }

    .tag-pill {
        display: inline-flex;
        align-items: center;
        padding: 0.2rem 0.6rem;
        border-radius: 999px;
        font-size: 0.72rem;
        border: 1px solid rgba(245, 196, 81, 0.6);
        color: #f5c451;
        background: rgba(0, 0, 0, 0.4);
    }

    .btn-gold {
        background: linear-gradient(135deg, #f5c451, #e0a629);
        color: #151515;
        border: none;
        border-radius: 999px;
        padding: 0.45rem 1.2rem;
        font-weight: 600;
        font-size: 0.9rem;
        box-shadow:
                0 10px 25px rgba(245, 196, 81, 0.48),
                0 0 0 1px rgba(0, 0, 0, 0.4);
        transition: transform 0.12s ease-out, box-shadow 0.12s ease-out, filter 0.12s ease-out;
    }
    .btn-gold:hover {
        transform: translateY(-1px);
        box-shadow:
                0 13px 30px rgba(245, 196, 81, 0.58);
        filter: brightness(1.03);
        color: #121212;
    }
    .btn-gold:active {
        transform: translateY(0);
        box-shadow: 0 5px 16px rgba(245, 196, 81, 0.38);
    }

    .btn-outline-gold {
        border-radius: 999px;
        border: 1px solid rgba(245, 196, 81, 0.8);
        color: #f5c451;
        background: transparent;
        padding: 0.25rem 0.7rem;
        font-size: 0.8rem;
    }
    .btn-outline-gold:hover {
        background: rgba(245, 196, 81, 0.12);
        color: #ffe08a;
    }

    .form-control-gold,
    .form-select-gold {
        background: rgba(6, 6, 14, 0.94);
        border-radius: 12px;
        border: 1px solid rgba(245, 196, 81, 0.35);
        color: #f8f9fa;
        box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.7);
        font-size: 0.9rem;
    }
    .form-control-gold:focus,
    .form-select-gold:focus {
        border-color: #f5c451;
        box-shadow:
                0 0 0 1px rgba(245, 196, 81, 0.65),
                0 0 0 4px rgba(245, 196, 81, 0.12);
        background: rgba(3, 3, 10, 0.98);
        color: #fff;
    }
    .form-control-gold::placeholder {
        color: rgba(204, 204, 204, 0.7);
    }

    .label-gold {
        font-size: 0.85rem;
        color: #d8d8e0;
        margin-bottom: 0.25rem;
    }

    .avatar-preview {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 2px solid rgba(245, 196, 81, 0.7);
        object-fit: cover;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.8);
        background: #000;
    }

    .badge-status {
        display: inline-flex;
        align-items: center;
        padding: 0.2rem 0.55rem;
        border-radius: 999px;
        font-size: 0.74rem;
    }

    .badge-status.open {
        background: rgba(40, 167, 69, 0.18);
        color: #7CFFAF;
        border: 1px solid rgba(40, 167, 69, 0.5);
    }

    .badge-status.closed {
        background: rgba(220, 53, 69, 0.18);
        color: #FF9A9A;
        border: 1px solid rgba(220, 53, 69, 0.55);
    }

    .custom-table {
        border-radius: 14px;
        overflow: hidden;
        background: rgba(5, 5, 15, 0.96);
        font-size: 0.85rem;
    }
    .custom-table thead {
        background: linear-gradient(135deg, #16161f, #1f1f29);
    }
    .custom-table thead th {
        border-bottom: 1px solid rgba(245, 196, 81, 0.45);
        color: #f5c451;
        font-weight: 600;
        padding: 0.6rem 0.75rem;
    }
    .custom-table tbody td {
        border-color: rgba(255, 255, 255, 0.04) !important;
        padding: 0.55rem 0.75rem;
        vertical-align: middle;
        color: #e1e1eb;
    }
    .custom-table tbody tr:hover {
        background: rgba(245, 196, 81, 0.06);
    }

    .truncate-text {
        max-width: 340px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .custom-modal .modal-content.glassy-card {
        background: radial-gradient(circle at top left, rgba(245, 196, 81, 0.16), rgba(4, 4, 10, 0.98));
        border-radius: 18px;
        border: 1px solid rgba(245, 196, 81, 0.4);
        box-shadow:
                0 24px 60px rgba(0, 0, 0, 0.9),
                0 0 0 1px rgba(0, 0, 0, 0.9);
        color: #f8f9fa;
        backdrop-filter: blur(18px);
        -webkit-backdrop-filter: blur(18px);
        position: relative;
    }
    .custom-modal .modal-content.glassy-card::before {
        content: "";
        position: absolute;
        inset: -20%;
        background:
                radial-gradient(circle at 8% 0, rgba(245, 196, 81, 0.3), transparent 55%),
                radial-gradient(circle at 100% 100%, rgba(80, 120, 255, 0.18), transparent 45%);
        opacity: 0.33;
        mix-blend-mode: color-dodge;
        pointer-events: none;
        z-index: -1;
    }

    .modal-header .btn-close-white {
        filter: invert(1) grayscale(1);
    }

    .section-gap {
        margin-bottom: 1.5rem;
    }

    .ck-editor__editable {
        min-height: 180px;
        background: rgba(6, 6, 14, 0.94);
        border-radius: 12px !important;
        border: 1px solid rgba(245, 196, 81, 0.35) !important;
        color: #f8f9fa !important;
    }

    .ck.ck-toolbar {
        border-radius: 12px 12px 0 0 !important;
        border-color: rgba(245, 196, 81, 0.35) !important;
        background: #13131b !important;
    }

    @media (max-width: 991.98px) {
        .page-wrapper {
            padding: 0.75rem;
        }
        .admin-card {
            padding: 1.2rem 1.2rem;
            border-radius: 16px;
        }
    }
</style>

<!-- =========================
     Page Content
========================= -->
<div class="page-wrapper container-fluid">
    <div class="row g-4">

        <!-- PROFILE SECTION -->
        <div class="col-12 col-xl-4 section-gap">
            <div class="admin-card h-100">
                <div class="admin-card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="admin-card-title gold-text mb-1">ویرایش پروفایل</h2>
                        <p class="muted-text mb-0" style="font-size: 0.78rem;">
                            اطلاعات پایه‌ای شما در وی‌کارت
                        </p>
                    </div>
                    <span class="tag-pill">
                        پروفایل
                    </span>
                </div>

                <form action="handlers/profile_update.php"
                      method="post"
                      enctype="multipart/form-data"
                      class="row g-3">
                    <input type="hidden" name="profile_id"
                           value="<?= htmlspecialchars($profile['ID'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

                    <div class="col-12 d-flex flex-column align-items-center">
                        <div class="mb-2">
                            <?php if (!empty($profile['Pprofilepic'])): ?>
                                <img src="<?= htmlspecialchars($profile['Pprofilepic'], ENT_QUOTES, 'UTF-8'); ?>"
                                     alt="Profile Picture"
                                     class="avatar-preview">
                            <?php else: ?>
                                <div class="avatar-preview d-flex align-items-center justify-content-center muted-text">
                                    بدون تصویر
                                </div>
                            <?php endif; ?>
                        </div>
                        <label class="btn btn-outline-gold btn-sm mt-1">
                            انتخاب تصویر
                            <input type="file" name="Pprofilepic" accept="image/*" hidden>
                        </label>
                    </div>

                    <div class="col-12">
                        <label class="label-gold">نام و نام خانوادگی</label>
                        <input type="text"
                               name="Pname"
                               class="form-control form-control-gold"
                               value="<?= htmlspecialchars($profile['Pname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                               placeholder="مثلاً: ماهان رضایی">
                    </div>

                    <div class="col-12">
                        <label class="label-gold">عنوان شغلی / پوزیشن</label>
                        <input type="text"
                               name="Pposition"
                               class="form-control form-control-gold"
                               value="<?= htmlspecialchars($profile['Pposition'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                               placeholder="مثلاً: Full-stack Web Developer">
                    </div>

                    <div class="col-12">
                        <label class="label-gold">ایمیل</label>
                        <input type="email"
                               name="Pemail"
                               class="form-control form-control-gold"
                               value="<?= htmlspecialchars($profile['Pemail'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                               placeholder="you@example.com">
                    </div>

                    <div class="col-md-6">
                        <label class="label-gold">شماره تماس</label>
                        <input type="text"
                               name="Pphonenumber"
                               class="form-control form-control-gold"
                               value="<?= htmlspecialchars($profile['Pphonenumber'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                               placeholder="0912...">
                    </div>

                    <div class="col-md-6">
                        <label class="label-gold">وضعیت همکاری</label>
                        <select name="Pcooperationstatus" class="form-select form-select-gold">
                            <option value="0"
                                    <?= (int)($profile['Pcooperationstatus'] ?? 0) === 0 ? 'selected' : ''; ?>>
                                در حال حاضر آزاد نیستم
                            </option>
                            <option value="1"
                                    <?= (int)($profile['Pcooperationstatus'] ?? 0) === 1 ? 'selected' : ''; ?>>
                                آماده همکاری / پروژه جدید
                            </option>
                        </select>
                        <div class="mt-1">
                            <?php if ((int)($profile['Pcooperationstatus'] ?? 0) === 1): ?>
                                <span class="badge-status open">قابل همکاری</span>
                            <?php else: ?>
                                <span class="badge-status closed">عدم پذیرش همکاری</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="label-gold">موقعیت مکانی</label>
                        <input type="text"
                               name="location"
                               class="form-control form-control-gold"
                               value="<?= htmlspecialchars($profile['location'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                               placeholder="مثلاً: تهران، ایران">
                    </div>

                    <div class="col-12 d-flex justify-content-end mt-2">
                        <button type="submit" class="btn btn-gold">
                            ذخیره پروفایل
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ABOUT ME + SKILLS & CARDS + COMPANIES -->
        <div class="col-12 col-xl-8 section-gap">
            <div class="row g-4">

                <!-- ABOUT ME & SKILLS -->
                <div class="col-12">
                    <div class="admin-card">
                        <div class="admin-card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="admin-card-title gold-text mb-1">درباره من و مهارت‌ها</h2>
                                <p class="muted-text mb-0" style="font-size: 0.78rem;">
                                    متن معرفی و لیست مهارت‌ها در وی‌کارت
                                </p>
                            </div>
                            <span class="tag-pill">About Me</span>
                        </div>

                        <form action="handlers/aboutme_update.php"
                              method="post"
                              class="row g-3">
                            <input type="hidden" name="aboutme_id"
                                   value="<?= htmlspecialchars($aboutMe['ID'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="profile_id"
                                   value="<?= htmlspecialchars($profile['ID'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

                            <div class="col-12">
                                <label class="label-gold">متن درباره من</label>
                                <textarea id="aboutme-editor"
                                          name="aboutme"
                                          class="form-control form-control-gold"
                                          rows="6"><?= htmlspecialchars($aboutMe['aboutme'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>

                            <div class="col-12">
                                <label class="label-gold">مهارت‌ها</label>
                                <textarea id="skills-editor"
                                          name="skills"
                                          class="form-control form-control-gold"
                                          rows="4"><?= htmlspecialchars($aboutMe['skills'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>

                            <div class="col-12 d-flex justify-content-end mt-1">
                                <button type="submit" class="btn btn-gold">
                                    ذخیره درباره من
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- ABOUT ME CARDS (INFO CARDS) -->
                <div class="col-12">
                    <div class="admin-card">
                        <div class="admin-card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="admin-card-title gold-text mb-1">کارت‌های درباره من</h2>
                                <p class="muted-text mb-0" style="font-size: 0.78rem;">
                                    کارت‌های توضیحی کوتاه برای نمایش در ویکارت
                                </p>
                            </div>
                            <button class="btn btn-gold btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#cardAddModal">
                                + افزودن کارت جدید
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-dark table-striped table-hover align-middle custom-table">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>عنوان</th>
                                    <th>آیکن / برچسب</th>
                                    <th>متن کوتاه</th>
                                    <th class="text-center">عملیات</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if (!empty($aboutMeCards)): ?>
                                    <?php foreach ($aboutMeCards as $index => $card): ?>
                                        <tr>
                                            <td><?= $index + 1; ?></td>
                                            <td><?= htmlspecialchars($card['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars($card['subtitle'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="truncate-text">
                                                <?= htmlspecialchars(mb_strimwidth($card['description'] ?? '', 0, 80, '...', 'UTF-8'), ENT_QUOTES, 'UTF-8'); ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button type="button"
                                                            class="btn btn-outline-gold"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#cardViewModal<?= $card['ID']; ?>">
                                                        مشاهده
                                                    </button>
                                                    <button type="button"
                                                            class="btn btn-outline-warning"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#cardEditModal<?= $card['ID']; ?>">
                                                        ویرایش
                                                    </button>
                                                    <button type="button"
                                                            class="btn btn-outline-danger"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#cardDeleteModal<?= $card['ID']; ?>">
                                                        حذف
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- View Card Modal -->
                                        <div class="modal fade custom-modal"
                                             id="cardViewModal<?= $card['ID']; ?>"
                                             tabindex="-1"
                                             aria-hidden="true">
                                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                                <div class="modal-content glassy-card">
                                                    <div class="modal-header border-0">
                                                        <h5 class="modal-title gold-text">
                                                            مشاهده کارت: <?= htmlspecialchars($card['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                        </h5>
                                                        <button type="button"
                                                                class="btn-close btn-close-white"
                                                                data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <h6 class="text-muted mb-1">عنوان</h6>
                                                            <p class="mb-0">
                                                                <?= htmlspecialchars($card['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                            </p>
                                                        </div>
                                                        <div class="mb-3">
                                                            <h6 class="text-muted mb-1">زیرعنوان / آیکن</h6>
                                                            <p class="mb-0">
                                                                <?= htmlspecialchars($card['subtitle'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                            </p>
                                                        </div>
                                                        <div class="mb-0">
                                                            <h6 class="text-muted mb-1">توضیحات</h6>
                                                            <p class="mb-0">
                                                                <?= nl2br(htmlspecialchars($card['description'] ?? '', ENT_QUOTES, 'UTF-8')); ?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0">
                                                        <button type="button"
                                                                class="btn btn-secondary"
                                                                data-bs-dismiss="modal">
                                                            بستن
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Edit Card Modal -->
                                        <div class="modal fade custom-modal"
                                             id="cardEditModal<?= $card['ID']; ?>"
                                             tabindex="-1"
                                             aria-hidden="true">
                                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                                <div class="modal-content glassy-card">
                                                    <div class="modal-header border-0">
                                                        <h5 class="modal-title gold-text">ویرایش کارت</h5>
                                                        <button type="button"
                                                                class="btn-close btn-close-white"
                                                                data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                    </div>
                                                    <form action="handlers/aboutmecard_update.php"
                                                          method="post">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="card_id"
                                                                   value="<?= (int)$card['ID']; ?>">
                                                            <input type="hidden" name="aboutme_id"
                                                                   value="<?= (int)$aboutMe['ID']; ?>">

                                                            <div class="mb-3">
                                                                <label class="label-gold">عنوان</label>
                                                                <input type="text"
                                                                       name="title"
                                                                       class="form-control form-control-gold"
                                                                       value="<?= htmlspecialchars($card['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                                       required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="label-gold">زیرعنوان / آیکن</label>
                                                                <input type="text"
                                                                       name="subtitle"
                                                                       class="form-control form-control-gold"
                                                                       value="<?= htmlspecialchars($card['subtitle'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                            </div>
                                                            <div class="mb-2">
                                                                <label class="label-gold">توضیحات</label>
                                                                <textarea name="description"
                                                                          class="form-control form-control-gold"
                                                                          rows="4"
                                                                          required><?= htmlspecialchars($card['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer border-0">
                                                            <button type="button"
                                                                    class="btn btn-secondary"
                                                                    data-bs-dismiss="modal">
                                                                انصراف
                                                            </button>
                                                            <button type="submit"
                                                                    class="btn btn-gold">
                                                                ذخیره تغییرات
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Delete Card Modal -->
                                        <div class="modal fade custom-modal"
                                             id="cardDeleteModal<?= $card['ID']; ?>"
                                             tabindex="-1"
                                             aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content glassy-card">
                                                    <div class="modal-header border-0">
                                                        <h5 class="modal-title text-danger">حذف کارت</h5>
                                                        <button type="button"
                                                                class="btn-close btn-close-white"
                                                                data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                    </div>
                                                    <form action="handlers/aboutmecard_delete.php"
                                                          method="post">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="card_id"
                                                                   value="<?= (int)$card['ID']; ?>">
                                                            <p class="mb-0">
                                                                آیا از حذف کارت
                                                                <span class="gold-text">
                                                                    <?= htmlspecialchars($card['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                                </span>
                                                                مطمئن هستید؟
                                                            </p>
                                                        </div>
                                                        <div class="modal-footer border-0">
                                                            <button type="button"
                                                                    class="btn btn-secondary"
                                                                    data-bs-dismiss="modal">
                                                                انصراف
                                                            </button>
                                                            <button type="submit"
                                                                    class="btn btn-danger">
                                                                بله، حذف شود
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            هنوز کارتی ثبت نشده است. از دکمه «افزودن کارت جدید» استفاده کنید.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- COMPANIES EXPERIENCE (AboutMeExp) -->
                <div class="col-12">
                    <div class="admin-card">
                        <div class="admin-card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="admin-card-title gold-text mb-1">تجربه همکاری با شرکت‌ها</h2>
                                <p class="muted-text mb-0" style="font-size: 0.78rem;">
                                    شرکت‌هایی که با آن‌ها همکاری داشته‌اید
                                </p>
                            </div>
                            <button class="btn btn-gold btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#expAddModal">
                                + افزودن شرکت جدید
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-dark table-striped table-hover align-middle custom-table">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>نام شرکت</th>
                                    <th>توضیحات کوتاه</th>
                                    <th>لینک پروفایل / وب‌سایت</th>
                                    <th class="text-center">عملیات</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if (!empty($aboutMeExps)): ?>
                                    <?php foreach ($aboutMeExps as $index => $exp): ?>
                                        <tr>
                                            <td><?= $index + 1; ?></td>
                                            <td><?= htmlspecialchars($exp['companyname'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="truncate-text">
                                                <?= htmlspecialchars(mb_strimwidth($exp['companytext'] ?? '', 0, 80, '...', 'UTF-8'), ENT_QUOTES, 'UTF-8'); ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($exp['companyprofile'])): ?>
                                                    <a href="<?= htmlspecialchars($exp['companyprofile'], ENT_QUOTES, 'UTF-8'); ?>"
                                                       target="_blank"
                                                       class="link-gold">
                                                        مشاهده پروفایل
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">ثبت نشده</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button type="button"
                                                            class="btn btn-outline-gold"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#expViewModal<?= $exp['ID']; ?>">
                                                        مشاهده
                                                    </button>
                                                    <button type="button"
                                                            class="btn btn-outline-warning"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#expEditModal<?= $exp['ID']; ?>">
                                                        ویرایش
                                                    </button>
                                                    <button type="button"
                                                            class="btn btn-outline-danger"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#expDeleteModal<?= $exp['ID']; ?>">
                                                        حذف
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- View Experience Modal -->
                                        <div class="modal fade custom-modal"
                                             id="expViewModal<?= $exp['ID']; ?>"
                                             tabindex="-1"
                                             aria-hidden="true">
                                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                                <div class="modal-content glassy-card">
                                                    <div class="modal-header border-0">
                                                        <h5 class="modal-title gold-text">
                                                            جزئیات شرکت: <?= htmlspecialchars($exp['companyname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                        </h5>
                                                        <button type="button"
                                                                class="btn-close btn-close-white"
                                                                data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <h6 class="text-muted mb-1">نام شرکت</h6>
                                                            <p class="mb-0">
                                                                <?= htmlspecialchars($exp['companyname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                            </p>
                                                        </div>
                                                        <div class="mb-3">
                                                            <h6 class="text-muted mb-1">توضیحات</h6>
                                                            <p class="mb-0">
                                                                <?= nl2br(htmlspecialchars($exp['companytext'] ?? '', ENT_QUOTES, 'UTF-8')); ?>
                                                            </p>
                                                        </div>
                                                        <div class="mb-0">
                                                            <h6 class="text-muted mb-1">پروفایل / لینک</h6>
                                                            <?php if (!empty($exp['companyprofile'])): ?>
                                                                <a href="<?= htmlspecialchars($exp['companyprofile'], ENT_QUOTES, 'UTF-8'); ?>"
                                                                   target="_blank"
                                                                   class="link-gold">
                                                                    <?= htmlspecialchars($exp['companyprofile'], ENT_QUOTES, 'UTF-8'); ?>
                                                                </a>
                                                            <?php else: ?>
                                                                <span class="text-muted">ثبت نشده</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0">
                                                        <button type="button"
                                                                class="btn btn-secondary"
                                                                data-bs-dismiss="modal">
                                                            بستن
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Edit Experience Modal -->
                                        <div class="modal fade custom-modal"
                                             id="expEditModal<?= $exp['ID']; ?>"
                                             tabindex="-1"
                                             aria-hidden="true">
                                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                                <div class="modal-content glassy-card">
                                                    <div class="modal-header border-0">
                                                        <h5 class="modal-title gold-text">ویرایش شرکت</h5>
                                                        <button type="button"
                                                                class="btn-close btn-close-white"
                                                                data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                    </div>
                                                    <form action="handlers/aboutexp_update.php"
                                                          method="post">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="exp_id"
                                                                   value="<?= (int)$exp['ID']; ?>">
                                                            <input type="hidden" name="aboutme_id"
                                                                   value="<?= (int)$aboutMe['ID']; ?>">

                                                            <div class="mb-3">
                                                                <label class="label-gold">نام شرکت</label>
                                                                <input type="text"
                                                                       name="companyname"
                                                                       class="form-control form-control-gold"
                                                                       value="<?= htmlspecialchars($exp['companyname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                                       required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="label-gold">توضیحات</label>
                                                                <textarea name="companytext"
                                                                          class="form-control form-control-gold"
                                                                          rows="4"
                                                                          required><?= htmlspecialchars($exp['companytext'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                                            </div>
                                                            <div class="mb-2">
                                                                <label class="label-gold">لینک پروفایل / وب‌سایت شرکت</label>
                                                                <input type="text"
                                                                       name="companyprofile"
                                                                       class="form-control form-control-gold"
                                                                       value="<?= htmlspecialchars($exp['companyprofile'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer border-0">
                                                            <button type="button"
                                                                    class="btn btn-secondary"
                                                                    data-bs-dismiss="modal">
                                                                انصراف
                                                            </button>
                                                            <button type="submit"
                                                                    class="btn btn-gold">
                                                                ذخیره تغییرات
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Delete Experience Modal -->
                                        <div class="modal fade custom-modal"
                                             id="expDeleteModal<?= $exp['ID']; ?>"
                                             tabindex="-1"
                                             aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content glassy-card">
                                                    <div class="modal-header border-0">
                                                        <h5 class="modal-title text-danger">حذف شرکت</h5>
                                                        <button type="button"
                                                                class="btn-close btn-close-white"
                                                                data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                    </div>
                                                    <form action="handlers/aboutexp_delete.php"
                                                          method="post">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="exp_id"
                                                                   value="<?= (int)$exp['ID']; ?>">
                                                            <p class="mb-0">
                                                                آیا از حذف شرکت
                                                                <span class="gold-text">
                                                                    <?= htmlspecialchars($exp['companyname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                                </span>
                                                                مطمئن هستید؟
                                                            </p>
                                                        </div>
                                                        <div class="modal-footer border-0">
                                                            <button type="button"
                                                                    class="btn btn-secondary"
                                                                    data-bs-dismiss="modal">
                                                                انصراف
                                                            </button>
                                                            <button type="submit"
                                                                    class="btn btn-danger">
                                                                بله، حذف شود
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            هنوز شرکتی ثبت نشده است. از دکمه «افزودن شرکت جدید» استفاده کنید.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div><!-- /row g-4 -->
        </div><!-- /col-xl-8 -->

    </div><!-- /row -->
</div><!-- /page-wrapper -->

<!-- Add Card Modal -->
<div class="modal fade custom-modal"
     id="cardAddModal"
     tabindex="-1"
     aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content glassy-card">
            <div class="modal-header border-0">
                <h5 class="modal-title gold-text">افزودن کارت جدید</h5>
                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <form action="handlers/aboutmecard_create.php"
                  method="post">
                <div class="modal-body">
                    <input type="hidden" name="aboutme_id"
                           value="<?= (int)$aboutMe['ID']; ?>">

                    <div class="mb-3">
                        <label class="label-gold">عنوان</label>
                        <input type="text"
                               name="title"
                               class="form-control form-control-gold"
                               placeholder="مثلاً: تجربه کاری"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="label-gold">زیرعنوان / آیکن</label>
                        <input type="text"
                               name="subtitle"
                               class="form-control form-control-gold"
                               placeholder="مثلاً: 5+ سال تجربه">
                    </div>

                    <div class="mb-2">
                        <label class="label-gold">توضیحات</label>
                        <textarea name="description"
                                  class="form-control form-control-gold"
                                  rows="4"
                                  placeholder="توضیح کوتاه درباره این کارت..."
                                  required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        انصراف
                    </button>
                    <button type="submit"
                            class="btn btn-gold">
                        ثبت کارت
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Experience Modal -->
<div class="modal fade custom-modal"
     id="expAddModal"
     tabindex="-1"
     aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content glassy-card">
            <div class="modal-header border-0">
                <h5 class="modal-title gold-text">افزودن شرکت جدید</h5>
                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <form action="handlers/aboutexp_create.php"
                  method="post">
                <div class="modal-body">
                    <input type="hidden" name="aboutme_id"
                           value="<?= (int)$aboutMe['ID']; ?>">

                    <div class="mb-3">
                        <label class="label-gold">نام شرکت</label>
                        <input type="text"
                               name="companyname"
                               class="form-control form-control-gold"
                               placeholder="مثلاً: شرکت فناوران هوشمند"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="label-gold">توضیحات</label>
                        <textarea name="companytext"
                                  class="form-control form-control-gold"
                                  rows="4"
                                  placeholder="توضیح مختصری درباره نقش، پروژه‌ها و تکنولوژی‌های استفاده‌شده..."
                                  required></textarea>
                    </div>

                    <div class="mb-2">
                        <label class="label-gold">لینک پروفایل / وب‌سایت شرکت</label>
                        <input type="text"
                               name="companyprofile"
                               class="form-control form-control-gold"
                               placeholder="https://example.com">
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        انصراف
                    </button>
                    <button type="submit"
                            class="btn btn-gold">
                        ثبت شرکت
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- CKEditor (Classic) -->
<script src="https://cdn.ckeditor.com/ckeditor5/41.2.0/classic/ckeditor.js"></script>
<script>
    const editorConfig = {
        language: 'fa',
        toolbar: [
            'heading', '|',
            'bold', 'italic', 'link', '|',
            'bulletedList', 'numberedList', '|',
            'undo', 'redo'
        ]
    };

    document.addEventListener('DOMContentLoaded', function () {
        const aboutMeElement = document.querySelector('#aboutme-editor');
        const skillsElement = document.querySelector('#skills-editor');

        if (aboutMeElement) {
            ClassicEditor
                .create(aboutMeElement, editorConfig)
                .catch(error => console.error(error));
        }

        if (skillsElement) {
            ClassicEditor
                .create(skillsElement, editorConfig)
                .catch(error => console.error(error));
        }
    });
</script>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
