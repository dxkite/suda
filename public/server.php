<?php

define('SUDA_APP', __DIR__ . '/../app');
define('SUDA_DATA', __DIR__ . '/../app/data');
define('SUDA_SYSTEM', __DIR__ . '/../suda');
define('SUDA_PUBLIC', __DIR__);
define('SUDA_DEBUG', true);
define('SUDA_DEBUG_LEVEL', 'info');
define('SUDA_APP_MANIFEST', SUDA_APP . '/manifest');
define('SUDA_SWOOLE_IP', '127.0.0.1');
define('SUDA_SWOOLE_PORT', 80);


require_once SUDA_SYSTEM.'/loader/swoole.php';