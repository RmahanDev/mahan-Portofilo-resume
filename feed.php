<?php
// RSS feed of the blog, served at /feed.xml (see .htaccess).
require __DIR__ . '/app/bootstrap.php';

header('Content-Type: application/rss+xml; charset=utf-8');

$x = static fn(string $text): string => htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
$posts = rows('SELECT slug, title, category, excerpt, cover, date_text, created_at, updated_at FROM posts WHERE is_published = 1 ORDER BY sort, id DESC LIMIT 20');
$siteTitle = setting('seo_blog_title') !== '' ? setting('seo_blog_title') : setting('profile_name');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
  <channel>
    <title><?= $x($siteTitle) ?></title>
    <link><?= $x(url_for('blog')) ?></link>
    <description><?= $x(setting('seo_blog_description') !== '' ? setting('seo_blog_description') : setting('site_description')) ?></description>
    <language>fa-IR</language>
    <atom:link href="<?= $x(url_for('feed.xml')) ?>" rel="self" type="application/rss+xml" />
<?php foreach ($posts as $post): $link = url_for('Blogs/' . rawurlencode($post['slug'])); $date = jalali_text_to_iso($post['date_text']) ?? substr($post['created_at'], 0, 10); ?>
    <item>
      <title><?= $x($post['title']) ?></title>
      <link><?= $x($link) ?></link>
      <guid isPermaLink="true"><?= $x($link) ?></guid>
      <pubDate><?= date(DATE_RSS, strtotime($date . ' 09:00:00')) ?></pubDate>
<?php if ($post['category'] !== ''): ?>
      <category><?= $x($post['category']) ?></category>
<?php endif; ?>
      <description><?= $x($post['excerpt']) ?></description>
    </item>
<?php endforeach; ?>
  </channel>
</rss>
