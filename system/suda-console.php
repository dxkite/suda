<?php
defined('APP_DIR') or define('APP_DIR',__DIR__.'/../app');
define('ROOT_PATH',dirname(__DIR__));
require_once __DIR__.'/src/suda/core/Autoloader.php';  
Autoloader::init();
// 初始化包含路径
Autoloader::addIncludePath(__DIR__.'/src');

suda\core\System::init();
suda\core\Config::set('console',true);
suda\core\ApplicationManager::getInstance()->console(APP_DIR);
