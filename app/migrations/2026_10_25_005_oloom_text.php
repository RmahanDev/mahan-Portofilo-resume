<?php
/**
 * The medical-university rack project text, split into boxed sections (scope, physical infrastructure, software and security,
 * result) with a short fact row. Only replaces the original text; a body edited in the admin is left alone.
 */

declare(strict_types=1);

return static function (PDO $pdo): void {
    $stmt = $pdo->prepare("SELECT id, body_html FROM projects WHERE slug = 'oloom'");
    $stmt->execute();
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    // The original body starts with a red inline-styled warning paragraph and has no boxed sections yet.
    if (!$project || strpos((string) $project['body_html'], 'color: red; font-weight: bold') === false || strpos((string) $project['body_html'], 'info-box') !== false) {
        return;
    }

    $pdo->prepare('UPDATE projects SET body_html = ? WHERE id = ?')
        ->execute([(string) file_get_contents(__DIR__ . '/content/oloom.body.html'), (int) $project['id']]);
};
