<?php
/**
 * Content/schema updates for databases that were installed from an older version.
 *
 * Every file in app/migrations/ returns a function(PDO $pdo): void and runs exactly once per database,
 * in file-name order. The installer runs them after seeding, and the site runs any pending ones
 * on the first request after a deploy, so pushing to GitHub is enough to update an installed site.
 * Migrations must be safe to run twice (look rows up by slug) and must use the $pdo they are given.
 */

declare(strict_types=1);

/** @return string[] migration names (file names without .php), oldest first */
function migration_names(): array
{
    $files = glob(__DIR__ . '/migrations/*.php') ?: [];
    sort($files);
    return array_map(static fn(string $f): string => basename($f, '.php'), $files);
}

/** Cheap check done on every request: compares the stored version with the newest file. */
function migrations_pending(): bool
{
    $names = migration_names();
    return $names !== [] && setting('schema_version') !== end($names);
}

/**
 * Applies every migration this database has not seen yet.
 *
 * @return string[] names that were applied now
 */
function run_migrations(PDO $pdo): array
{
    $pdo->exec('CREATE TABLE IF NOT EXISTS migrations (
        name       VARCHAR(120) NOT NULL PRIMARY KEY,
        applied_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

    // Two visitors may arrive together right after a deploy; only one of them migrates.
    $lock = $pdo->query("SELECT GET_LOCK('portfolio_migrate', 0)");
    $got = (int) $lock->fetchColumn();
    $lock->closeCursor();
    if (!$got) {
        return [];
    }

    $applied = [];
    try {
        $done = $pdo->query('SELECT name FROM migrations')->fetchAll(PDO::FETCH_COLUMN);
        foreach (migration_names() as $name) {
            if (in_array($name, $done, true)) {
                continue;
            }
            $migrate = require __DIR__ . '/migrations/' . $name . '.php';
            $pdo->beginTransaction();
            try {
                $migrate($pdo);
                $pdo->prepare('INSERT INTO migrations (name) VALUES (?)')->execute([$name]);
                if ($pdo->inTransaction()) {
                    $pdo->commit();
                }
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                throw new RuntimeException($name . ': ' . $e->getMessage(), 0, $e);
            }
            $applied[] = $name;
        }
        $names = migration_names();
        $pdo->prepare('INSERT INTO settings (k, v) VALUES (?, ?) ON DUPLICATE KEY UPDATE v = VALUES(v)')
            ->execute(['schema_version', (string) end($names)]);
        $pdo->prepare('DELETE FROM settings WHERE k = ?')->execute(['migration_error']);
    } finally {
        $pdo->query("SELECT RELEASE_LOCK('portfolio_migrate')")->closeCursor();
    }
    return $applied;
}

/**
 * Called from bootstrap on public and admin requests. A failing migration must never take the site down:
 * the error is stored (shown on the admin dashboard) and the attempt is repeated at most every five minutes.
 */
function auto_migrate(): void
{
    try {
        if (!migrations_pending()) {
            if (setting('migration_error') !== '') {
                q('DELETE FROM settings WHERE k = ?', ['migration_error']);
            }
            return;
        }
        $last = setting('migration_error');
        if ($last !== '' && time() - (int) strtok($last, '|') < 300) {
            return;
        }
        run_migrations(db());
        settings(true);
    } catch (Throwable $e) {
        try {
            save_setting('migration_error', time() . '|' . $e->getMessage());
        } catch (Throwable $ignored) {
            // The database itself is unreachable; the normal page code reports that.
        }
    }
}
