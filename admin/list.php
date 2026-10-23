<?php
require dirname(__DIR__) . '/app/admin/core.php';
require_admin();

$res = resource((string) ($_GET['r'] ?? ''));
$table = $res['table'];

$where = '';
$params = [];
$filterKey = $res['filter'] ?? null;
$filterVal = $filterKey !== null ? (string) ($_GET['f'] ?? '') : '';
if ($filterKey !== null && $filterVal !== '') {
    $where = "WHERE {$filterKey} = ?";
    $params[] = $filterVal;
}
$order = $filterKey !== null ? "{$filterKey}, sort, id" : 'sort, id';
$items = rows("SELECT * FROM {$table} {$where} ORDER BY {$order}", $params);

$pageTitle = $res['title'];
$menu = in_array($res['key'], ['projects', 'posts'], true) ? $res['key'] : 'settings-' . ($res['group'] ?? 'portfolio');

require dirname(__DIR__) . '/app/admin/layout_top.php';
?>
                        <div class="admin-toolbar">
                            <a class="btn btn--small" href="edit.php?r=<?= e($res['key']) ?><?= $filterVal !== '' ? '&amp;f=' . e(urlencode($filterVal)) : '' ?>"><i class="feathericon-plus"></i> <?= e($res['singular']) ?> جدید</a>
<?php if (!empty($res['group'])): ?>
                            <a class="admin-link" href="settings.php?g=<?= e($res['group']) ?>"><i class="feathericon-arrow-right"></i> بازگشت به <?= e(setting_groups()[$res['group']]['title']) ?></a>
<?php elseif ($res['key'] === 'categories'): ?>
                            <a class="admin-link" href="list.php?r=projects"><i class="feathericon-arrow-right"></i> بازگشت به نمونه کارها</a>
<?php elseif ($res['key'] === 'projects'): ?>
                            <a class="admin-link" href="list.php?r=categories"><i class="feathericon-layers"></i> مدیریت دسته‌بندی‌ها</a>
<?php endif; ?>
<?php if ($filterKey !== null): $filterDef = $res['fields'][$filterKey]; ?>
                            <form method="get" class="admin-filter">
                                <input type="hidden" name="r" value="<?= e($res['key']) ?>">
                                <select class="custom-select" name="f" onchange="this.form.submit()">
                                    <option value="">همه <?= e($filterDef['label']) ?>ها</option>
<?php foreach (field_options($filterDef) as $k => $l): ?>
                                    <option value="<?= e((string) $k) ?>"<?= (string) $k === $filterVal ? ' selected' : '' ?>><?= e($l) ?></option>
<?php endforeach; ?>
                                </select>
                            </form>
<?php endif; ?>
                        </div>

<?php if (!$items): ?>
                        <div class="box box-inner admin-empty">هنوز موردی ثبت نشده است.</div>
<?php else: ?>
                        <div class="admin-table-wrap box box-inner">
                            <table class="admin-table">
                                <thead>
                                    <tr>
<?php foreach ($res['list'] as $col => $label): ?>
                                        <th><?= $label === 'image' ? '' : e($label) ?></th>
<?php endforeach; ?>
                                        <th class="admin-table__actions">عملیات</th>
                                    </tr>
                                </thead>
                                <tbody>
<?php foreach ($items as $i => $item):
    $sameGroup = static fn(?array $o): bool => $o !== null && ($filterKey === null || $o[$filterKey] == $item[$filterKey]);
    $isFirst = !$sameGroup($items[$i - 1] ?? null);
    $isLast = !$sameGroup($items[$i + 1] ?? null);
?>
                                    <tr>
<?php foreach ($res['list'] as $col => $label):
    $def = $res['fields'][$col] ?? ['type' => 'text'];
    $v = $item[$col];
?>
<?php if ($label === 'image'): ?>
                                        <td class="admin-table__thumb"><?php if ($v !== ''): ?><img src="<?= asset($v, '../') ?>" alt=""><?php endif; ?></td>
<?php elseif ($def['type'] === 'bool'): ?>
                                        <td><span class="admin-pill<?= $v ? ' admin-pill--on' : '' ?>"><?= $v ? 'نمایش' : 'پیش‌نویس' ?></span></td>
<?php elseif ($def['type'] === 'select'): ?>
                                        <td><?= e(field_options($def)[$v] ?? $v) ?></td>
<?php elseif ($def['type'] === 'percent'): ?>
                                        <td><?= (int) $v ?>%</td>
<?php else: ?>
                                        <td><a href="edit.php?r=<?= e($res['key']) ?>&amp;id=<?= (int) $item['id'] ?>"><?= e(mb_strimwidth(strip_tags((string) $v), 0, 70, '…')) ?></a></td>
<?php endif; ?>
<?php endforeach; ?>
                                        <td class="admin-table__actions">
                                            <form method="post" action="action.php" class="admin-inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="r" value="<?= e($res['key']) ?>">
                                                <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                                <input type="hidden" name="back" value="<?= e($_SERVER['REQUEST_URI'] ?? '') ?>">
                                                <button class="admin-icon-btn" name="do" value="up" title="بالاتر"<?= $isFirst ? ' disabled' : '' ?>><i class="feathericon-arrow-up"></i></button>
                                                <button class="admin-icon-btn" name="do" value="down" title="پایین‌تر"<?= $isLast ? ' disabled' : '' ?>><i class="feathericon-arrow-down"></i></button>
                                                <a class="admin-icon-btn" href="edit.php?r=<?= e($res['key']) ?>&amp;id=<?= (int) $item['id'] ?>" title="ویرایش"><i class="feathericon-edit-2"></i></a>
<?php if (!empty($res['view_url']) && !empty($item['is_published'])): ?>
                                                <a class="admin-icon-btn" href="../<?= e(str_replace('{slug}', rawurlencode($item['slug']), $res['view_url'])) ?>" target="_blank" title="مشاهده در سایت"><i class="feathericon-eye"></i></a>
<?php endif; ?>
                                                <button class="admin-icon-btn admin-icon-btn--danger" name="do" value="delete" title="حذف" data-confirm="این مورد حذف شود؟"><i class="feathericon-trash-2"></i></button>
                                            </form>
                                        </td>
                                    </tr>
<?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
<?php endif; ?>
<?php require dirname(__DIR__) . '/app/admin/layout_bottom.php';
