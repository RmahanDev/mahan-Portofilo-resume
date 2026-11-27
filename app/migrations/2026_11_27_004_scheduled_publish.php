<?php
/**
 * Scheduled publishing: a nullable publish_at (Tehran time) on posts and projects. While it is set the item is a draft;
 * publish_due_items() in app/schedule.php publishes it once that time has come.
 */

declare(strict_types=1);

return static function (PDO $pdo): void {
    foreach (['posts', 'projects'] as $table) {
        $has = $pdo->query("SHOW COLUMNS FROM {$table} LIKE 'publish_at'");
        $exists = $has->fetch() !== false;
        $has->closeCursor();
        if (!$exists) {
            $pdo->exec("ALTER TABLE {$table} ADD COLUMN publish_at DATETIME NULL DEFAULT NULL AFTER is_published");
        }
    }
};
