<?php
require_once __DIR__.'/src/suda/core/Autoloader.php';  
suda\core\Autoloader::init();
suda\core\Autoloader::addIncludePath(__DIR__.'/src');
suda\core\System::init();
suda\core\ApplicationManager::getInstance()->run(APP_DIR);