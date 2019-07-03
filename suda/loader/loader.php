<?php
// 运行时长
set_time_limit(0);
// 忽略用户断开
ignore_user_abort(true);
// 基本常量
defined('SUDA_TIMEZONE') or define('SUDA_TIMEZONE', 'PRC');
defined('SUDA_SYSTEM') or define('SUDA_SYSTEM', dirname(__DIR__));
defined('SUDA_RESOURCE') or define('SUDA_RESOURCE', SUDA_SYSTEM.'/resource');
defined('SUDA_START_TIME') or define('SUDA_START_TIME', microtime(true));
defined('SUDA_START_MEMORY') or define('SUDA_START_MEMORY', memory_get_usage());
defined('SUDA_DEBUG') or define('SUDA_DEBUG', false);
defined('SUDA_DEBUG_LEVEL') or define('SUDA_DEBUG_LEVEL', 'debug');
// 定义版本
define('SUDA_VERSION', '3.0.0');
// 设置默认时区
date_default_timezone_set(SUDA_TIMEZONE);
// 调试模式
if (SUDA_DEBUG) {
    error_reporting(E_ALL);
}

// PHP版本检查
if (version_compare(PHP_VERSION, '7.2.0', '<')) {
    require SUDA_RESOURCE.'/suda_panic.php';
    suda_panic('Kernal Panic', 'your current  php vesion is '.PHP_VERSION.', please use 7.2.0 + to run this program!');
}

// HOME PAHT GET
if (!defined('USER_HOME_PATH')) {
    // for linux
    if (array_key_exists('HOME', $_SERVER)) {
        define('USER_HOME_PATH', $_SERVER['HOME']);
    }
    // for windows
    elseif (array_key_exists('HOMEDRIVE', $_SERVER) && array_key_exists('HOMEPATH', $_SERVER)) {
        define('USER_HOME_PATH', $_SERVER['HOMEDRIVE'].$_SERVER['HOMEPATH']);
    }
    // for unknown
    else {
        define('USER_HOME_PATH', getcwd());
    }
}
// 加载器
require_once SUDA_SYSTEM .'/src/framework/loader/Path.php';
require_once SUDA_SYSTEM .'/src/framework/loader/PathTrait.php';
require_once SUDA_SYSTEM .'/src/framework/loader/PathInterface.php';
require_once SUDA_SYSTEM .'/src/framework/loader/IncludeManager.php';
require_once SUDA_SYSTEM .'/src/framework/loader/Loader.php';
