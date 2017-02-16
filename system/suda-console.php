<?php
defined('APP_DIR') or define('APP_DIR',__DIR__.'/../app');
define('ROOT_PATH',dirname(__DIR__));
require_once __DIR__.'/src/suda/core/System.php';
suda\core\System::init();
// 初始化包含路径
System::addIncludePath(__DIR__.'/src');
Config::set('console',true);
ApplicationManager::getInstance()->console(APP_DIR);
