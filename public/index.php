<?php
define('SUDA_APP', __DIR__ . '/../app');
define('SUDA_DATA', __DIR__ . '/../data');
define('SUDA_SYSTEM', __DIR__ . '/../suda');
define('SUDA_PUBLIC', __DIR__);
define('SUDA_DEBUG', true);
define('SUDA_DEBUG_LEVEL', 'info');

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    die('please run <b>composer install</b> to install requirements');
}

require_once SUDA_SYSTEM . '/loader/main.php';


