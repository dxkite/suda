<?php
define('APP_DIR',__DIR__.'/../application');
require_once __DIR__.'/src/suda/core/System.php';

define('SYS_DIR',__DIR__);
define('SYS_RES',__DIR__.'/resource');

suda\core\System::init();
// 初始化包含路径
System::addIncludePath(__DIR__.'/src');
ApplicationManager::getInstance()->console(APP_DIR);