<?php
require_once __DIR__.'/__autoload.php'; 
defined('DEBUG') or     define('DEBUG',true);
defined('APP_DIR') or define('APP_DIR',__DIR__.'/../app');
define('ROOT_PATH',dirname(__DIR__));
suda\core\System::init();
suda\core\Config::set('console',true);
suda\core\System::console(APP_DIR);
