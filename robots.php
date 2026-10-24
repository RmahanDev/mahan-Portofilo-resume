<?php
// robots.txt, served at /robots.txt (see .htaccess). Generated so the sitemap line always has the real address.
require __DIR__ . '/app/bootstrap.php';

header('Content-Type: text/plain; charset=utf-8');

echo "User-agent: *\n";
echo "Allow: /\n";
echo "Disallow: /admin/\n";
echo "Disallow: /install/\n";
echo "Disallow: /app/\n";
echo "Disallow: /config/\n";
echo "Disallow: /contact-send.php\n";
echo "\n";
echo 'Sitemap: ' . url_for('sitemap.xml') . "\n";
