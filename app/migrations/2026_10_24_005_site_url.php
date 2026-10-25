<?php
/**
 * The site lives at https://co.shainika.com, so canonical links, the sitemap and robots.txt use it
 * from the first request. An address already saved in the admin is kept.
 */

declare(strict_types=1);

return static function (PDO $pdo): void {
    $read = $pdo->prepare('SELECT v FROM settings WHERE k = ?');
    $read->execute(['site_url']);
    $current = $read->fetchColumn();
    $read->closeCursor();
    if ($current === false || trim((string) $current) === '') {
        $pdo->prepare('INSERT INTO settings (k, v) VALUES (?, ?) ON DUPLICATE KEY UPDATE v = VALUES(v)')
            ->execute(['site_url', 'https://co.shainika.com']);
    }
};
