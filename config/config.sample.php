<?php
// Copy to config.php (the web installer at /install does this for you).
// config.php is git-ignored so pushing to GitHub never overwrites it on the host.
return [
    'db' => [
        'host'    => 'localhost',
        'name'    => 'cpaneluser_portfolio',
        'user'    => 'cpaneluser_portfolio',
        'pass'    => '',
        'charset' => 'utf8mb4',
    ],
    'debug' => false,
];
