<?php
// Row actions from list pages: reorder and delete.
require dirname(__DIR__) . '/app/admin/core.php';
require_admin();
verify_post();

$res = resource((string) ($_POST['r'] ?? ''));
$table = $res['table'];
$id = (int) ($_POST['id'] ?? 0);
$back = (string) ($_POST['back'] ?? '');
// Only allow redirects back into the admin panel.
$back = preg_match('~^/[^/\\\\]~', $back) && strpos($back, '/admin/') !== false ? $back : 'list.php?r=' . $res['key'];

$item = row("SELECT * FROM {$table} WHERE id = ?", [$id]);
if (!$item) {
    flash('error', 'مورد پیدا نشد.');
    redirect($back);
}

switch ($_POST['do'] ?? '') {
    case 'delete':
        q("DELETE FROM {$table} WHERE id = ?", [$id]);
        flash('success', $res['singular'] . ' حذف شد.');
        break;

    case 'up':
    case 'down':
        // Renumber the whole list first so equal sort values can still be swapped.
        $scope = '';
        $params = [];
        if (!empty($res['filter'])) {
            $scope = "WHERE {$res['filter']} = ?";
            $params[] = $item[$res['filter']];
        }
        $ids = array_column(rows("SELECT id FROM {$table} {$scope} ORDER BY sort, id", $params), 'id');
        $pos = array_search($item['id'], $ids);
        $swap = $_POST['do'] === 'up' ? $pos - 1 : $pos + 1;
        if ($pos !== false && isset($ids[$swap])) {
            [$ids[$pos], $ids[$swap]] = [$ids[$swap], $ids[$pos]];
        }
        $stmt = db()->prepare("UPDATE {$table} SET sort = ? WHERE id = ?");
        foreach ($ids as $i => $rowId) {
            $stmt->execute([$i + 1, $rowId]);
        }
        break;
}

redirect($back);
