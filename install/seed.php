<?php
/**
 * Creates the tables and fills them with the content that used to live in the static HTML pages.
 */

declare(strict_types=1);

function run_schema(PDO $pdo, string $sqlFile): void
{
    $sql = file_get_contents($sqlFile);
    $sql = preg_replace('~^\s*--.*$~m', '', $sql);
    foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) {
        $pdo->exec($statement);
    }
}

function seed_database(PDO $pdo, array $d): void
{
    $insert = static function (string $table, array $row) use ($pdo): int {
        $cols = array_keys($row);
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            implode(', ', $cols),
            implode(', ', array_fill(0, count($cols), '?'))
        );
        $pdo->prepare($sql)->execute(array_values($row));
        return (int) $pdo->lastInsertId();
    };

    $pdo->beginTransaction();
    try {
        foreach ($d['settings'] as $k => $v) {
            $pdo->prepare('INSERT INTO settings (k, v) VALUES (?, ?) ON DUPLICATE KEY UPDATE v = VALUES(v)')
                ->execute([$k, (string) $v]);
        }
        foreach ($d['socials'] as $i => $r) {
            $insert('socials', $r + ['sort' => $i + 1]);
        }
        foreach ($d['services'] as $i => $r) {
            $insert('services', $r + ['sort' => $i + 1]);
        }
        foreach ($d['skills'] as $i => $text) {
            $insert('skills', ['text' => $text, 'sort' => $i + 1]);
        }
        foreach ($d['experiences'] as $i => $r) {
            $insert('experiences', $r + ['sort' => $i + 1]);
        }
        foreach ($d['resume_items'] as $i => $r) {
            $insert('resume_items', $r + ['sort' => $i + 1]);
        }
        foreach ($d['skill_groups'] as $i => $g) {
            $gid = $insert('skill_groups', ['title' => $g['title'], 'sort' => $i + 1]);
            foreach ($g['bars'] as $j => $b) {
                $insert('skill_bars', $b + ['group_id' => $gid, 'sort' => $j + 1]);
            }
        }
        $catIds = [];
        foreach ($d['categories'] as $i => $c) {
            $catIds[$c['slug']] = $insert('categories', $c + ['sort' => $i + 1]);
        }
        foreach ($d['projects'] as $i => $p) {
            $images = $p['images'];
            $cats = $p['categories'];
            unset($p['images'], $p['categories']);
            $pid = $insert('projects', $p + ['sort' => $i + 1]);
            foreach ($cats as $slug) {
                if (isset($catIds[$slug])) {
                    $insert('project_categories', ['project_id' => $pid, 'category_id' => $catIds[$slug]]);
                }
            }
            foreach ($images as $j => $img) {
                $insert('project_images', $img + ['project_id' => $pid, 'sort' => $j + 1]);
            }
        }
        foreach ($d['posts'] as $i => $p) {
            $insert('posts', $p + ['sort' => $i + 1]);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}
