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

defined('D_START') or define('D_START', microtime(true));
defined('D_MEM') or define('D_MEM', memory_get_usage());
define('SUDA_VERSION', '1.2.15');
header('X-Powered-By: Suda/'.SUDA_VERSION);
// 报错函数检测
if (PHP_SAPI==='cli') {
    function suda_panic($error_type, $error_message, $error_code=null)
    {
        if (!is_null($error_code)) {
            $error_type =  $error_type .'('.$error_code.')';
        }
        die($error_type.':'.$error_message);
    }
} else {
    require_once __DIR__.'/resource/suda_panic.php';
}
/* PHP版本检测 */
if (version_compare(PHP_VERSION, '7.2.0', '<')) {
    suda_panic('Kernal Panic', 'your current  php vesion is '.PHP_VERSION.', please use 7.2.0 + to run this program!');
}
require_once __DIR__.'/src/suda/core/Autoloader.php';
suda\core\Autoloader::register();
