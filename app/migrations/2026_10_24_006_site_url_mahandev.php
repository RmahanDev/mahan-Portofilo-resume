<?php
/**
 * The site moved to https://mahandev.ir. The address from the previous migration (or an empty one) is replaced;
 * any other address saved in the admin is kept.
 */

declare(strict_types=1);

return static function (PDO $pdo): void {
    $read = $pdo->prepare('SELECT v FROM settings WHERE k = ?');
    $read->execute(['site_url']);
    $current = rtrim(trim((string) $read->fetchColumn()), '/');
    $read->closeCursor();
    if ($current === '' || $current === 'https://co.shainika.com') {
        $pdo->prepare('INSERT INTO settings (k, v) VALUES (?, ?) ON DUPLICATE KEY UPDATE v = VALUES(v)')
            ->execute(['site_url', 'https://mahandev.ir']);
    }
};
