<?php
/**
 * Repairs the cover and header images of the PHP2Shell article.
 * The article was written in the admin panel, so its rows carry image paths that do not exist on the host.
 * An image is replaced only when its current file is missing; working images and admin edits are left alone.
 */

declare(strict_types=1);

return static function (PDO $pdo): void {
    $slug = 'php2shell-php-critical-vulnerability';
    $root = dirname(__DIR__, 2) . '/';
    $coverFallback = 'assets/images/news/php2shell-cover.jpg';
    $headerFallback = 'assets/images/news/php2shell-header.jpg';

    $find = $pdo->prepare('SELECT id, cover, header_image FROM posts WHERE slug = ?');
    $find->execute([$slug]);
    $row = $find->fetch(PDO::FETCH_ASSOC);
    $find->closeCursor();
    if ($row === false) {
        return;
    }

    $pick = static function (?string $current, string $fallback) use ($root): string {
        $current = (string) $current;
        if ($current !== '' && is_file($root . ltrim($current, '/'))) {
            return $current;
        }
        return $fallback;
    };

    $cover = $pick($row['cover'], $coverFallback);
    $header = $pick($row['header_image'], $headerFallback);
    if ($cover === $row['cover'] && $header === $row['header_image']) {
        return;
    }

    $update = $pdo->prepare('UPDATE posts SET cover = ?, header_image = ? WHERE id = ?');
    $update->execute([$cover, $header, $row['id']]);
};
