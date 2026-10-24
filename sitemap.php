<?php
// XML sitemap, served at /sitemap.xml (see .htaccess). Lists every public page, project and article.
require __DIR__ . '/app/bootstrap.php';

header('Content-Type: application/xml; charset=utf-8');
header('X-Robots-Tag: noindex');

/** One <url> block; $image is a site-relative path or ''. */
function sitemap_entry(string $path, ?string $lastmod = null, string $image = '', string $imageTitle = ''): string
{
    $xml = "  <url>\n    <loc>" . htmlspecialchars(url_for($path), ENT_XML1) . "</loc>\n";
    if ($lastmod) {
        $xml .= '    <lastmod>' . $lastmod . "</lastmod>\n";
    }
    if ($image !== '') {
        $xml .= '    <image:image><image:loc>' . htmlspecialchars(absolute_url($image), ENT_XML1) . '</image:loc>';
        if ($imageTitle !== '') {
            $xml .= '<image:title>' . htmlspecialchars($imageTitle, ENT_XML1) . '</image:title>';
        }
        $xml .= "</image:image>\n";
    }
    return $xml . "  </url>\n";
}

$newest = static function (string $table): ?string {
    $date = row("SELECT MAX(updated_at) AS d FROM {$table} WHERE is_published = 1")['d'] ?? null;
    return $date ? date('c', strtotime($date)) : null;
};
$postsDate = $newest('posts');
$projectsDate = $newest('projects');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

echo sitemap_entry('', max($postsDate, $projectsDate) ?: null, 'assets/images/social.png');
echo sitemap_entry('resume');
echo sitemap_entry('portfolio', $projectsDate);
echo sitemap_entry('blog', $postsDate);
echo sitemap_entry('contact');

foreach (rows('SELECT slug, title, cover, header_image, updated_at FROM projects WHERE is_published = 1 ORDER BY sort, id') as $p) {
    echo sitemap_entry('Projects/' . rawurlencode($p['slug']), date('c', strtotime($p['updated_at'])), $p['header_image'] !== '' ? $p['header_image'] : $p['cover'], $p['title']);
}
foreach (rows('SELECT slug, title, cover, header_image, updated_at FROM posts WHERE is_published = 1 ORDER BY sort, id DESC') as $p) {
    echo sitemap_entry('Blogs/' . rawurlencode($p['slug']), date('c', strtotime($p['updated_at'])), $p['header_image'] !== '' ? $p['header_image'] : $p['cover'], $p['title']);
}

echo "</urlset>\n";
