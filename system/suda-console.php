<?php
define('APP_DIR',__DIR__.'/../application');
define('ROOT_PATH',dirname(__DIR__));
require_once __DIR__.'/src/suda/core/System.php';

define('SYS_DIR',__DIR__);
define('SYS_RES',__DIR__.'/resource');

suda\core\System::init();
// 初始化包含路径
System::addIncludePath(__DIR__.'/src');
Config::set('console',true);
ApplicationManager::getInstance()->console(APP_DIR);
