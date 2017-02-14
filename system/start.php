<?php
require_once __DIR__.'/src/suda/core/System.php';
suda\core\System::init();
// 初始化包含路径
System::addIncludePath(__DIR__.'/src');
ApplicationManager::run(APP_PATH);