<?php
/**
 * Illustrated covers and headers for the inventory/sales and live-chat projects, in the style of the plugin items.
 * The original images move into each project gallery. Projects whose cover was already changed in the admin are left alone.
 */

declare(strict_types=1);

return static function (PDO $pdo): void {
    $items = [
        'mahi'    => ['assets/images/projects/mahi/mahi9.png', 'assets/images/projects/inventory-sales.jpg', 'assets/images/projects/inventory-sales-header.jpg'],
        'chatapp' => ['assets/images/projects/chat/chat6.png', 'assets/images/projects/live-chat.jpg', 'assets/images/projects/live-chat-header.jpg'],
    ];

    $find   = $pdo->prepare('SELECT id FROM projects WHERE slug = ? AND cover = ? AND header_image = ?');
    $update = $pdo->prepare('UPDATE projects SET cover = ?, header_image = ? WHERE id = ?');
    $has    = $pdo->prepare('SELECT COUNT(*) FROM project_images WHERE project_id = ? AND path = ?');
    $add    = $pdo->prepare('INSERT INTO project_images (project_id, path, width, height, sort) VALUES (?, ?, ?, ?, ?)');

    foreach ($items as $slug => [$old, $cover, $header]) {
        $find->execute([$slug, $old, $old]);
        $id = (int) $find->fetchColumn();
        if ($id === 0) {
            continue;
        }
        $update->execute([$cover, $header, $id]);

        $has->execute([$id, $old]);
        if ((int) $has->fetchColumn() === 0) {
            $size = @getimagesize(dirname(__DIR__, 2) . '/' . $old) ?: [1920, 1080];
            $next = (int) $pdo->query('SELECT COALESCE(MAX(sort), 0) + 1 FROM project_images WHERE project_id = ' . $id)->fetchColumn();
            $add->execute([$id, $old, (int) $size[0], (int) $size[1], $next]);
        }
    }
};
