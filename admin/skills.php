<?php
// One-page editor for the resume skill bars: groups, bars and their percentages.
require dirname(__DIR__) . '/app/admin/core.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_post();
    $pdo = db();
    try {
        $pdo->beginTransaction();

        foreach ((array) ($_POST['group'] ?? []) as $gid => $g) {
            $gid = (int) $gid;
            if (!empty($g['delete'])) {
                q('DELETE FROM skill_groups WHERE id = ?', [$gid]);
                continue;
            }
            $title = trim((string) ($g['title'] ?? ''));
            if ($title === '') {
                throw new RuntimeException('عنوان گروه نمی‌تواند خالی باشد.');
            }
            q('UPDATE skill_groups SET title = ?, sort = ? WHERE id = ?', [$title, (int) ($g['sort'] ?? 0), $gid]);
        }

        foreach ((array) ($_POST['bar'] ?? []) as $bid => $b) {
            $bid = (int) $bid;
            if (!empty($b['delete'])) {
                q('DELETE FROM skill_bars WHERE id = ?', [$bid]);
                continue;
            }
            $label = trim((string) ($b['label'] ?? ''));
            if ($label === '') {
                throw new RuntimeException('نام مهارت نمی‌تواند خالی باشد. برای حذف، تیک «حذف» را بزنید.');
            }
            q(
                'UPDATE skill_bars SET label = ?, percent = ?, sort = ? WHERE id = ?',
                [$label, max(0, min(100, (int) ($b['percent'] ?? 0))), (int) ($b['sort'] ?? 0), $bid]
            );
        }

        // New bars typed into the "add" row of each group.
        foreach ((array) ($_POST['new_bar'] ?? []) as $gid => $b) {
            $label = trim((string) ($b['label'] ?? ''));
            if ($label === '') {
                continue;
            }
            $next = (int) row('SELECT COALESCE(MAX(sort), 0) + 1 AS n FROM skill_bars WHERE group_id = ?', [(int) $gid])['n'];
            q(
                'INSERT INTO skill_bars (group_id, label, percent, sort) VALUES (?, ?, ?, ?)',
                [(int) $gid, $label, max(0, min(100, (int) ($b['percent'] ?? 80))), $next]
            );
        }

        $newGroup = trim((string) ($_POST['new_group'] ?? ''));
        if ($newGroup !== '') {
            $next = (int) row('SELECT COALESCE(MAX(sort), 0) + 1 AS n FROM skill_groups')['n'];
            q('INSERT INTO skill_groups (title, sort) VALUES (?, ?)', [$newGroup, $next]);
        }

        $pdo->commit();
        flash('success', 'مهارت‌ها ذخیره شد.');
    } catch (RuntimeException $ex) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        flash('error', $ex->getMessage());
    }
    redirect('skills.php');
}

$groups = rows('SELECT * FROM skill_groups ORDER BY sort, id');
$bars = [];
foreach (rows('SELECT * FROM skill_bars ORDER BY sort, id') as $bar) {
    $bars[$bar['group_id']][] = $bar;
}

$pageTitle = 'مهارت‌ها و درصد توانایی';
$menu = 'skills';
require dirname(__DIR__) . '/app/admin/layout_top.php';
?>
                        <p class="admin-help">این نوارها در صفحه «رزومه» نمایش داده می‌شوند. اسم مهارت و درصد را تغییر بدهید، با تیک «حذف» مهارت را پاک کنید، و از ردیف آخر هر گروه یک نوار جدید اضافه کنید. همه تغییرات با یک دکمه ذخیره می‌شوند.</p>

                        <form method="post" class="admin-form">
                            <?= csrf_field() ?>

<?php foreach ($groups as $group): $gid = (int) $group['id']; ?>
                            <div class="box box-inner admin-skillgroup">
                                <div class="admin-skillgroup__head">
                                    <input type="text" class="input form-control" name="group[<?= $gid ?>][title]" value="<?= e($group['title']) ?>" placeholder="عنوان گروه" required>
                                    <label class="admin-skillgroup__sort">ترتیب <input type="number" class="input form-control" name="group[<?= $gid ?>][sort]" value="<?= (int) $group['sort'] ?>"></label>
                                    <label class="admin-gallery__del" title="با حذف گروه، همه نوارهای آن هم حذف می‌شوند"><input class="form-check-input" type="checkbox" name="group[<?= $gid ?>][delete]" value="1" data-confirm="این گروه با همه نوارهایش حذف شود؟"> حذف گروه</label>
                                </div>

<?php foreach ($bars[$gid] ?? [] as $bar): $bid = (int) $bar['id']; ?>
                                <div class="admin-skillbar">
                                    <input type="text" class="input form-control admin-skillbar__label" name="bar[<?= $bid ?>][label]" value="<?= e($bar['label']) ?>" dir="auto" placeholder="نام مهارت">
                                    <div class="admin-range">
                                        <input type="range" min="0" max="100" class="form-range" name="bar[<?= $bid ?>][percent]" value="<?= (int) $bar['percent'] ?>" data-percent-range>
                                        <span><?= (int) $bar['percent'] ?>%</span>
                                    </div>
                                    <input type="number" class="input form-control admin-skillbar__sort" name="bar[<?= $bid ?>][sort]" value="<?= (int) $bar['sort'] ?>" title="ترتیب">
                                    <label class="admin-gallery__del"><input class="form-check-input" type="checkbox" name="bar[<?= $bid ?>][delete]" value="1"> حذف</label>
                                </div>
<?php endforeach; ?>

                                <div class="admin-skillbar admin-skillbar--new">
                                    <input type="text" class="input form-control admin-skillbar__label" name="new_bar[<?= $gid ?>][label]" dir="auto" placeholder="+ مهارت جدید در این گروه (مثلا Docker)">
                                    <div class="admin-range">
                                        <input type="range" min="0" max="100" class="form-range" name="new_bar[<?= $gid ?>][percent]" value="80" data-percent-range>
                                        <span>80%</span>
                                    </div>
                                </div>
                            </div>
<?php endforeach; ?>

                            <div class="box box-inner admin-skillgroup admin-skillgroup--new">
                                <input type="text" class="input form-control" name="new_group" placeholder="+ گروه جدید (مثلا «دیتابیس و DevOps»)، بعد از ذخیره می‌توانید مهارت‌هایش را اضافه کنید">
                            </div>

                            <div class="admin-actions">
                                <button type="submit" class="btn"><i class="feathericon-save"></i> ذخیره همه تغییرات</button>
                            </div>
                        </form>
<?php require dirname(__DIR__) . '/app/admin/layout_bottom.php';
