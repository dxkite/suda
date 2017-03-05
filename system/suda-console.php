<?php
require_once __DIR__.'/__autoload.php'; 

defined('APP_DIR') or define('APP_DIR',__DIR__.'/../app');
define('ROOT_PATH',dirname(__DIR__));
suda\core\System::init();
suda\core\Config::set('console',true);
suda\core\ApplicationManager::getInstance()->console(APP_DIR);
