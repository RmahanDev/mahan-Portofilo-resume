<?php
/**
 * Visitor statistics: a visits table (one row per counted page view) and a lasting views counter on posts and projects.
 * The counters keep growing after old visit rows are pruned, so the public view counts never go down.
 */

declare(strict_types=1);

return static function (PDO $pdo): void {
    $pdo->exec('CREATE TABLE IF NOT EXISTS visits (
        id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        visitor    CHAR(32)     NOT NULL,
        ip         VARCHAR(45)  NOT NULL DEFAULT \'\',
        path       VARCHAR(255) NOT NULL,
        page_type  VARCHAR(20)  NOT NULL DEFAULT \'page\',
        ref_id     INT UNSIGNED NOT NULL DEFAULT 0,
        referrer   VARCHAR(190) NOT NULL DEFAULT \'\',
        device     VARCHAR(10)  NOT NULL DEFAULT \'\',
        browser    VARCHAR(20)  NOT NULL DEFAULT \'\',
        os         VARCHAR(20)  NOT NULL DEFAULT \'\',
        user_agent VARCHAR(400) NOT NULL DEFAULT \'\',
        created_at DATETIME     NOT NULL,
        KEY idx_visits_created (created_at),
        KEY idx_visits_visitor (visitor, path, created_at),
        KEY idx_visits_ref (page_type, ref_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

    foreach (['posts', 'projects'] as $table) {
        $has = $pdo->query("SHOW COLUMNS FROM {$table} LIKE 'views'");
        $exists = $has->fetch() !== false;
        $has->closeCursor();
        if (!$exists) {
            $pdo->exec("ALTER TABLE {$table} ADD COLUMN views INT UNSIGNED NOT NULL DEFAULT 0");
        }
    }

    // Secret mixed into the visitor fingerprint, so the stored hash cannot be turned back into an IP.
    $pdo->prepare('INSERT IGNORE INTO settings (k, v) VALUES (?, ?)')->execute(['analytics_salt', bin2hex(random_bytes(16))]);
};
