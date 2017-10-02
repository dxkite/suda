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

/* PHP版本检测 */
if (version_compare(PHP_VERSION, '7.0.0', '<')) {
    die('your current  php vesion is '.PHP_VERSION.', <span style="color:red">please use 7.0.0 + to run this program!</span>'."\r\n");
}
require_once __DIR__.'/src/suda/core/Autoloader.php';  
suda\core\Autoloader::init();