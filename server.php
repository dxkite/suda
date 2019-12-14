<?php
$host = $argv[1] ?? '127.0.0.1:9501';
list($ip, $port) = explode(':', $host);

defined('SUDA_APP') or define('SUDA_APP', __DIR__ . '/app');
defined('SUDA_DATA') or define('SUDA_DATA', __DIR__ . '/data');
defined('SUDA_SYSTEM') or define('SUDA_SYSTEM', __DIR__ . '/suda');
defined('SUDA_PUBLIC') or define('SUDA_PUBLIC', __DIR__);
defined('SUDA_DEBUG') or define('SUDA_DEBUG', true);
defined('SUDA_DEBUG_LEVEL') or define('SUDA_DEBUG_LEVEL', 'debug');
defined('SUDA_APP_MANIFEST') or define('SUDA_APP_MANIFEST', SUDA_APP . '/manifest');
defined('SUDA_DEBUG_LOG_PATH') or define('SUDA_DEBUG_LOG_PATH', SUDA_DATA . '/logs');
// 设置IP或者端口
defined('SUDA_SWOOLE_IP') or define('SUDA_SWOOLE_IP', $ip);
defined('SUDA_SWOOLE_PORT') or define('SUDA_SWOOLE_PORT', $port);

require_once __DIR__ . '/vendor/autoload.php';
require_once SUDA_SYSTEM . '/loader/swoole.php';