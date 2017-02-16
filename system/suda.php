<?php
require_once __DIR__.'/src/suda/core/System.php';
suda\core\System::init();
// 初始化包含路径
System::addIncludePath(__DIR__.'/src');
// var_dump(Request::url());
ApplicationManager::getInstance()->run(APP_DIR);