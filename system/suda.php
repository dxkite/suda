<?php

require_once __DIR__.'/src/suda/core/Autoloader.php';  
Autoloader::init();
// 初始化包含路径
Autoloader::addIncludePath(__DIR__.'/src');

suda\core\System::init();
// var_dump(Request::url());
suda\core\ApplicationManager::getInstance()->run(APP_DIR);