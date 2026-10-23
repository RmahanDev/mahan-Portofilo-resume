<?php
// Create / edit form for any resource defined in app/admin/resources.php.
require dirname(__DIR__) . '/app/admin/core.php';
require_admin();

$res = resource((string) ($_GET['r'] ?? ''));
$table = $res['table'];
$fields = $res['fields'];
$id = (int) ($_GET['id'] ?? 0);
$item = $id ? row("SELECT * FROM {$table} WHERE id = ?", [$id]) : null;
if ($id && !$item) {
    flash('error', 'مورد پیدا نشد.');
    redirect('list.php?r=' . $res['key']);
}

/** Columns that live on the row itself (not pivot/gallery helpers). */
$columns = array_filter($fields, static fn($d) => !in_array($d['type'], ['categories', 'gallery'], true));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_post();
    $data = [];
    try {
        foreach ($columns as $name => $def) {
            $data[$name] = read_field($name, $def);
        }

        foreach ($columns as $name => $def) {
            if ($def['type'] !== 'slug') {
                continue;
            }
            if ($data[$name] === '') {
                $source = (string) ($data[$def['from'] ?? ''] ?? '');
                $ascii = trim(strtolower(preg_replace('~[^A-Za-z0-9]+~', '-', $source)), '-');
                $data[$name] = $ascii !== '' ? $ascii : rtrim($res['key'], 's') . '-' . substr(bin2hex(random_bytes(3)), 0, 6);
            }
            $taken = row("SELECT id FROM {$table} WHERE {$name} = ? AND id <> ?", [$data[$name], $id]);
            if ($taken) {
                throw new RuntimeException('آدرس «' . $data[$name] . '» قبلا استفاده شده، یک آدرس دیگر انتخاب کنید.');
            }
        }

        db()->beginTransaction();
        if ($item) {
            $set = implode(', ', array_map(static fn($c) => "{$c} = ?", array_keys($data)));
            q("UPDATE {$table} SET {$set} WHERE id = ?", array_merge(array_values($data), [$id]));
        } else {
            $data['sort'] = (int) row("SELECT COALESCE(MAX(sort), 0) + 1 AS n FROM {$table}")['n'];
            $cols = implode(', ', array_keys($data));
            $marks = implode(', ', array_fill(0, count($data), '?'));
            q("INSERT INTO {$table} ({$cols}) VALUES ({$marks})", array_values($data));
            $id = (int) db()->lastInsertId();
        }

        if (isset($fields['categories'])) {
            q('DELETE FROM project_categories WHERE project_id = ?', [$id]);
            foreach ((array) ($_POST['categories'] ?? []) as $cid) {
                q('INSERT IGNORE INTO project_categories (project_id, category_id) VALUES (?, ?)', [$id, (int) $cid]);
            }
        }

        if (isset($fields['images'])) {
            foreach ((array) ($_POST['gallery'] ?? []) as $imgId => $g) {
                if (!empty($g['delete'])) {
                    q('DELETE FROM project_images WHERE id = ? AND project_id = ?', [(int) $imgId, $id]);
                } else {
                    q('UPDATE project_images SET sort = ? WHERE id = ? AND project_id = ?', [(int) ($g['sort'] ?? 0), (int) $imgId, $id]);
                }
            }
            $next = (int) row('SELECT COALESCE(MAX(sort), 0) AS n FROM project_images WHERE project_id = ?', [$id])['n'];
            foreach (upload_list('gallery_new') as $file) {
                $path = store_upload($file);
                if ($path !== null) {
                    [$w, $h] = image_size($path);
                    q('INSERT INTO project_images (project_id, path, width, height, sort) VALUES (?, ?, ?, ?, ?)', [$id, $path, $w, $h, ++$next]);
                }
            }
        }
        db()->commit();

        flash('success', $res['singular'] . ' ذخیره شد.');
        redirect('edit.php?r=' . $res['key'] . '&id=' . $id);
    } catch (RuntimeException $ex) {
        if (db()->inTransaction()) {
            db()->rollBack();
        }
        flash('error', $ex->getMessage());
        // Keep what the user typed so nothing is lost.
        $item = array_merge($item ?? [], $data, $_POST);
    }
}

$values = [];
foreach ($fields as $name => $def) {
    if ($def['type'] === 'categories') {
        $values[$name] = isset($_POST['categories'])
            ? array_map('intval', (array) $_POST['categories'])
            : ($id ? array_map('intval', array_column(rows('SELECT category_id FROM project_categories WHERE project_id = ?', [$id]), 'category_id')) : []);
    } elseif ($def['type'] !== 'gallery') {
        $values[$name] = $item[$name] ?? field_default($def);
        if ($name === ($res['filter'] ?? null) && !$item && isset($_GET['f'])) {
            $values[$name] = (string) $_GET['f'];
        }
    }
}
$gallery = ($id && isset($fields['images']))
    ? rows('SELECT * FROM project_images WHERE project_id = ? ORDER BY sort, id', [$id])
    : [];

$pageTitle = ($item && $id ? 'ویرایش ' : 'افزودن ') . $res['singular'];
$menu = in_array($res['key'], ['projects', 'posts'], true) ? $res['key'] : 'settings-' . ($res['group'] ?? 'portfolio');

require dirname(__DIR__) . '/app/admin/layout_top.php';
?>
                        <div class="admin-toolbar">
                            <a class="admin-link" href="list.php?r=<?= e($res['key']) ?>"><i class="feathericon-arrow-right"></i> بازگشت به <?= e($res['title']) ?></a>
<?php if ($id && !empty($res['view_url']) && !empty($item['is_published'])): ?>
                            <a class="admin-link" target="_blank" href="../<?= e(str_replace('{slug}', rawurlencode($item['slug']), $res['view_url'])) ?>"><i class="feathericon-eye"></i> مشاهده در سایت</a>
<?php endif; ?>
                        </div>

                        <form method="post" enctype="multipart/form-data" class="admin-form box box-inner">
                            <?= csrf_field() ?>
                            <div class="row">
<?php foreach ($fields as $name => $def): ?>
<?php if ($def['type'] === 'gallery'): ?>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="admin-label"><?= e($def['label']) ?></label>
<?php if ($gallery): ?>
                                        <div class="admin-gallery">
<?php foreach ($gallery as $img): ?>
                                            <div class="admin-gallery__item">
                                                <img src="<?= asset($img['path'], '../') ?>" alt="">
                                                <div class="admin-gallery__meta">
                                                    <label>ترتیب <input type="number" class="input form-control" name="gallery[<?= (int) $img['id'] ?>][sort]" value="<?= (int) $img['sort'] ?>"></label>
                                                    <label class="admin-gallery__del"><input class="form-check-input" type="checkbox" name="gallery[<?= (int) $img['id'] ?>][delete]" value="1"> حذف</label>
                                                </div>
                                            </div>
<?php endforeach; ?>
                                        </div>
<?php endif; ?>
                                        <label class="btn btn--small admin-image__upload mt-2"><i class="feathericon-upload"></i> افزودن تصاویر به گالری
                                            <input type="file" name="gallery_new[]" accept="image/*" multiple hidden data-count-label>
                                        </label>
                                        <span class="admin-help" data-count-output></span>
                                    </div>
                                </div>
<?php else: ?>
                                <?= render_field($name, $def, $values[$name]) ?>
<?php endif; ?>
<?php endforeach; ?>
                            </div>
                            <div class="admin-actions">
                                <button type="submit" class="btn"><i class="feathericon-save"></i> ذخیره</button>
                            </div>
                        </form>
<?php require dirname(__DIR__) . '/app/admin/layout_bottom.php';
