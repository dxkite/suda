<?php

defined('DEBUG') or define('DEBUG', true);
defined('USER_HOME') or define('USER_HOME', DIRECTORY_SEPARATOR ===  '/' ?$_SERVER["HOME"]:$_SERVER["HOMEDRIVE"].$_SERVER["HOMEPATH"]);

require_once __DIR__.'/__autoload.php';

defined('DATA_DIR') or define('DATA_DIR', \suda\core\Autoloader::realPath('~/.suda'));
defined('APP_PUBLIC') or define('APP_PUBLIC', DATA_DIR.'/public');

suda\core\System::init();
suda\core\Config::set('console', true);
