<?php
require dirname(__DIR__) . '/app/admin/core.php';
require_admin();

$groups = setting_groups();
$g = (string) ($_GET['g'] ?? 'profile');
if (!isset($groups[$g])) {
    redirect('settings.php');
}
$group = $groups[$g];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_post();
    try {
        $values = [];
        foreach ($group['fields'] as $key => $def) {
            $values[$key] = (string) read_field($key, $def);
        }
        foreach ($values as $key => $value) {
            save_setting($key, $value);
        }
        flash('success', 'تغییرات ذخیره شد.');
    } catch (RuntimeException $ex) {
        flash('error', $ex->getMessage());
    }
    redirect('settings.php?g=' . $g);
}

$pageTitle = $group['title'];
$menu = 'settings-' . $g;
$all = resources();

require dirname(__DIR__) . '/app/admin/layout_top.php';
?>
                        <form method="post" enctype="multipart/form-data" class="admin-form box box-inner">
                            <?= csrf_field() ?>
                            <div class="row">
<?php foreach ($group['fields'] as $key => $def): ?>
                                <?= render_field($key, $def, setting($key)) ?>
<?php endforeach; ?>
                            </div>
                            <div class="admin-actions">
                                <button type="submit" class="btn"><i class="feathericon-save"></i> ذخیره تغییرات</button>
                            </div>
                        </form>

<?php if (!empty($group['related'])): ?>
                        <h2 class="title title--h2 mt-4">بخش‌های این صفحه</h2>
                        <div class="row">
<?php foreach ($group['related'] as $rk): $res = $all[$rk]; $n = (int) row("SELECT COUNT(*) c FROM {$res['table']}")['c']; ?>
                            <div class="col-12 col-lg-6">
                                <a class="case-item box box-inner admin-stat" href="list.php?r=<?= e($rk) ?>">
                                    <span class="box icon-box"><i class="font-icon <?= e($res['icon']) ?>"></i></span>
                                    <div>
                                        <h3 class="title title--h3"><?= e($res['title']) ?></h3>
                                        <p class="case-item__caption"><?= $n ?> مورد · مدیریت و ویرایش</p>
                                    </div>
                                </a>
                            </div>
<?php endforeach; ?>
<?php if ($g === 'resume'): ?>
                            <div class="col-12 col-lg-6">
                                <a class="case-item box box-inner admin-stat" href="skills.php">
                                    <span class="box icon-box"><i class="font-icon feathericon-bar-chart-2"></i></span>
                                    <div>
                                        <h3 class="title title--h3">مهارت‌ها و درصد توانایی</h3>
                                        <p class="case-item__caption">افزودن مهارت و گروه جدید، تعیین درصد · ویرایش</p>
                                    </div>
                                </a>
                            </div>
<?php endif; ?>
                        </div>
<?php endif; ?>
<?php require dirname(__DIR__) . '/app/admin/layout_bottom.php';
