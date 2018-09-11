<?php
/**
 * Suda FrameWork
 *
 * An open source application development framework for PHP 7.0.0 or newer
 *
 * Copyright (c)  2017 DXkite
 *
 * @category   PHP FrameWork
 * @package    Suda
 * @copyright  Copyright (c) DXkite
 * @license    MIT
 * @link       https://github.com/DXkite/suda
 * @version    since 1.2.9
 */

 // 定义版本
define('SUDA_VERSION', '2.0.1');

// 注册基本常量
defined('D_START') or define('D_START', microtime(true));
defined('D_MEM') or define('D_MEM', memory_get_usage());
defined('ROOT_PATH') or define('ROOT_PATH', dirname(__DIR__));
defined('SYSTEM_DIR') or define('SYSTEM_DIR', __DIR__);
defined('SYSTEM_RESOURCE') or define('SYSTEM_RESOURCE', SYSTEM_DIR.'/resource');
defined('DEBUG') or define('DEBUG', false);
defined('IS_LINUX') or define('IS_LINUX', DIRECTORY_SEPARATOR ===  '/');
defined('IS_CONSOLE') or define('IS_CONSOLE', PHP_SAPI==='cli');


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
suda\core\Autoloader::register();
