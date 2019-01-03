<?php
/**
 * Suda FrameWork
 *
 * An open source application development framework for PHP 7.2.0 or newer
 *
 * Copyright (c)  2017-2018 DXkite
 *
 * @category   PHP FrameWork
 * @package    Suda
 * @copyright  Copyright (c) DXkite
 * @license    MIT
 * @link       https://github.com/DXkite/suda
 * @version    since 1.2.9
 */

// 定义版本
define('SUDA_VERSION', '2.0.3');
// 环境信息
defined('IS_LINUX') or define('IS_LINUX', DIRECTORY_SEPARATOR ===  '/');
defined('IS_CONSOLE') or define('IS_CONSOLE', PHP_SAPI === 'cli');
defined('IN_PHAR') or define('IN_PHAR', strpos(__DIR__, 'phar://') === 0);
defined('DEBUG') or define('DEBUG', $_SERVER['SUDA_DEBUG'] ?? $_ENV['SUDA_DEBUG'] ?? false);
// 注册基本常量
defined('SUDA_START_TIME') or define('SUDA_START_TIME', microtime(true));
defined('SUDA_START_MEMORY') or define('SUDA_START_MEMORY', memory_get_usage());
defined('SUDA_ENTRANCE') or define('SUDA_ENTRANCE', get_included_files()[intval(IN_PHAR)]);
// 基本信息
defined('ROOT_PATH') or define('ROOT_PATH', dirname(__DIR__));
defined('SYSTEM_DIR') or define('SYSTEM_DIR', __DIR__);
defined('SYSTEM_RESOURCE') or define('SYSTEM_RESOURCE', SYSTEM_DIR.'/resource');

if (DEBUG) {
    error_reporting(E_ALL);
}

// 报错函数检测
if (IS_CONSOLE) {
    function suda_panic($error_type, $error_message, $error_code=null)
    {
        if (!is_null($error_code)) {
            $error_type =  $error_type .'('.$error_code.')';
        }
        die($error_type.':'.$error_message);
    }
} else {
    header('X-Powered-By: Suda/'.SUDA_VERSION);
    require_once __DIR__.'/resource/suda_panic.php';
}

/* PHP版本检测 */
if (version_compare(PHP_VERSION, '7.2.0', '<')) {
    suda_panic('Kernal Panic', 'your current  php vesion is '.PHP_VERSION.', please use 7.2.0 + to run this program!');
}

require_once __DIR__.'/src/suda/core/Autoloader.php';
require_once __DIR__.'/functions.php';
\suda\core\Autoloader::register();
\suda\core\Debug::init();