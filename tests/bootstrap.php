<?php
require_once __DIR__.'/../vendor/autoload.php';

define('TEST_RESOURCE', __DIR__.'/resource');
define('SUDA_DATA', TEST_RESOURCE.'/runtime-data');
define('SUDA_SYSTEM', __DIR__.'/../suda');
define('SUDA_APP', SUDA_SYSTEM.'/resource/app');
// 基本常量
defined('SUDA_TIMEZONE') or define('SUDA_TIMEZONE', 'PRC');
defined('SUDA_SYSTEM') or define('SUDA_SYSTEM', dirname(__DIR__));
defined('SUDA_RESOURCE') or define('SUDA_RESOURCE', SUDA_SYSTEM.'/resource');
defined('SUDA_START_TIME') or define('SUDA_START_TIME', microtime(true));
defined('SUDA_START_MEMORY') or define('SUDA_START_MEMORY', memory_get_usage());
defined('SUDA_DEBUG') or define('SUDA_DEBUG', false);
defined('SUDA_DEBUG_LEVEL') or define('SUDA_DEBUG_LEVEL', 'trace');
// 定义版本
define('SUDA_VERSION', '3.0.0');