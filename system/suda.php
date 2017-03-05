<?php
require_once __DIR__.'/__autoload.php';  
suda\core\System::init();
suda\core\ApplicationManager::getInstance()->run(APP_DIR);