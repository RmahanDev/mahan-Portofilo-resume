<?php
/**
 * New illustrated cover and header for the medical-university rack project, in the style of the plugin items.
 * The original photo moves into the project gallery. Projects whose cover was already changed in the admin are left alone.
 */

declare(strict_types=1);

return static function (PDO $pdo): void {
    $old = 'assets/images/projects/oloom/oloom5.jpg';

    $stmt = $pdo->prepare('SELECT id FROM projects WHERE slug = ? AND cover = ? AND header_image = ?');
    $stmt->execute(['oloom', $old, $old]);
    $id = (int) $stmt->fetchColumn();
    if ($id === 0) {
        return;
    }

    $pdo->prepare('UPDATE projects SET cover = ?, header_image = ? WHERE id = ?')
        ->execute(['assets/images/projects/rack-network.jpg', 'assets/images/projects/rack-network-header.jpg', $id]);

    $has = $pdo->prepare('SELECT COUNT(*) FROM project_images WHERE project_id = ? AND path = ?');
    $has->execute([$id, $old]);
    if ((int) $has->fetchColumn() === 0) {
        $next = (int) $pdo->query('SELECT COALESCE(MAX(sort), 0) + 1 FROM project_images WHERE project_id = ' . $id)->fetchColumn();
        $pdo->prepare('INSERT INTO project_images (project_id, path, width, height, sort) VALUES (?, ?, 1920, 1200, ?)')
            ->execute([$id, $old, $next]);
    }
};
