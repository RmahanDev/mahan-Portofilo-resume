<?php
/**
 * A cleaner portfolio taxonomy: the first categories say what KIND of work an item is (website, web app, plugin, network),
 * the last ones are technology tags (WordPress, front-end). The category listed first for a project is its primary one and
 * becomes the label on the card, so card_category / category_label only need filling to override it.
 */

declare(strict_types=1);

return static function (PDO $pdo): void {
    $one = static function (string $sql, array $params = []) use ($pdo) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $value = $stmt->fetchColumn();
        $stmt->closeCursor();
        return $value;
    };

    // Old slug => new slug for categories that only change name and meaning.
    $renames = ['web-development' => 'website', 'IT' => 'network'];
    foreach ($renames as $old => $new) {
        if ($one('SELECT COUNT(*) FROM categories WHERE slug = ?', [$new]) == 0 && $one('SELECT COUNT(*) FROM categories WHERE slug = ?', [$old]) > 0) {
            $pdo->prepare('UPDATE categories SET slug = ? WHERE slug = ?')->execute([$new, $old]);
        }
    }

    $wanted = [
        'website'   => ['وب‌سایت', 1],
        'web-app'   => ['وب‌اپلیکیشن و سامانه', 2],
        'plugin'    => ['افزونه', 3],
        'network'   => ['شبکه و زیرساخت', 4],
        'wordpress' => ['وردپرس', 5],
        'frontend'  => ['طراحی رابط (فرانت‌اند)', 6],
    ];
    $ids = [];
    foreach ($wanted as $slug => [$name, $sort]) {
        $id = (int) $one('SELECT id FROM categories WHERE slug = ?', [$slug]);
        if ($id > 0) {
            $pdo->prepare('UPDATE categories SET name = ?, sort = ? WHERE id = ?')->execute([$name, $sort, $id]);
        } else {
            $pdo->prepare('INSERT INTO categories (slug, name, sort) VALUES (?, ?, ?)')->execute([$slug, $name, $sort]);
            $id = (int) $pdo->lastInsertId();
        }
        $ids[$slug] = $id;
    }

    // The old "design" category was a duplicate of "website": move its projects over, then drop it.
    $design = (int) $one("SELECT id FROM categories WHERE slug = 'design'");
    if ($design > 0) {
        $pdo->prepare('INSERT IGNORE INTO project_categories (project_id, category_id) SELECT project_id, ? FROM project_categories WHERE category_id = ?')
            ->execute([$ids['website'], $design]);
        $pdo->prepare('DELETE FROM categories WHERE id = ?')->execute([$design]);
    }

    // Known projects get exact assignments; anything added later keeps what it has.
    $assign = [
        'veresk'                             => ['web-app', 'frontend'],
        'academia'                           => ['website', 'wordpress'],
        'amozesh'                            => ['website', 'frontend'],
        'ershad'                             => ['network'],
        'oloom'                              => ['network'],
        'mahi'                               => ['web-app', 'frontend'],
        'sisishop'                           => ['website', 'wordpress'],
        'shainika'                           => ['website', 'wordpress'],
        'chatapp'                            => ['web-app', 'frontend'],
        'moaref-wordpress-plugin'            => ['plugin', 'wordpress'],
        'shainika-sms-auth-wordpress-plugin' => ['plugin', 'wordpress'],
        'petfelix-website'                   => ['website', 'wordpress'],
        'cobaltwear-website'                 => ['website', 'wordpress'],
        'iraniorkide-website'                => ['website', 'wordpress'],
        'iranicert-website'                  => ['website', 'wordpress'],
    ];
    foreach ($assign as $slug => $cats) {
        $pid = (int) $one('SELECT id FROM projects WHERE slug = ?', [$slug]);
        if ($pid === 0) {
            continue;
        }
        $pdo->prepare('DELETE FROM project_categories WHERE project_id = ?')->execute([$pid]);
        foreach ($cats as $c) {
            $pdo->prepare('INSERT INTO project_categories (project_id, category_id) VALUES (?, ?)')->execute([$pid, $ids[$c]]);
        }
    }

    // The old hand-typed labels are replaced by the category names unless someone wrote something else there.
    $old = ['طراحی وب', 'وردپرس', 'شبکه', 'افزونه وردپرس', 'افزونهٔ وردپرس'];
    $marks = implode(',', array_fill(0, count($old), '?'));
    $pdo->prepare("UPDATE projects SET card_category = '' WHERE card_category IN ($marks)")->execute($old);
    $pdo->prepare("UPDATE projects SET category_label = '' WHERE category_label IN ($marks)")->execute($old);
};
