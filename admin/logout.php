<?php
require dirname(__DIR__) . '/app/admin/core.php';

$_SESSION = [];
session_destroy();
redirect('login.php');
