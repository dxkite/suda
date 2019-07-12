<?php
$host = $argv[1] ?? '127.0.0.1:9501';
list($ip, $port) = explode(':', $host);

define('SUDA_APP', __DIR__ . '/app');
define('SUDA_DATA', __DIR__ . '/data');
define('SUDA_SYSTEM', __DIR__ . '/suda');
define('SUDA_PUBLIC', __DIR__);
define('SUDA_DEBUG', true);
define('SUDA_DEBUG_LEVEL', 'debug');
define('SUDA_APP_MANIFEST', SUDA_APP . '/manifest');

// 设置IP或者端口
define('SUDA_SWOOLE_IP', $ip);
define('SUDA_SWOOLE_PORT', $port);


require_once SUDA_SYSTEM.'/loader/swoole.php';