<?php
require_once __DIR__.'/src/suda/core/System.php';
suda\core\System::init();
// var_dump(Request::url());
ApplicationManager::getInstance()->run(APP_DIR);