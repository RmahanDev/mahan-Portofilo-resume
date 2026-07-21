<?php
echo 'Host: ' . $_SERVER['HTTP_HOST'] . '<br>';
echo 'Server: ' . $_SERVER['SERVER_NAME'] . '<br>';
echo 'INI: ' . php_ini_loaded_file() . '<br>';
echo 'Drivers: ';
print_r(PDO::getAvailableDrivers());
