<?php
/* PHP版本检测 */
if (version_compare(PHP_VERSION, '7.0.0', '<')) {
    die('please use php 7.0.0 + to run this program!'."\r\n");
}
require_once __DIR__.'/src/suda/core/Autoloader.php';  
suda\core\Autoloader::init();
suda\core\Autoloader::addIncludePath(__DIR__.'/src');