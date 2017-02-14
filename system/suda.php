<?php
require_once __DIR__.'/src/suda/core/System.php';
define('ROOT_PATH',dirname(__DIR__));
define('SYS_DIR',__DIR__);
define('SYS_RES',__DIR__.'/resource');

suda\core\System::init();
// 初始化包含路径
System::addIncludePath(__DIR__.'/src');
ApplicationManager::getInstance()->run(APP_DIR);