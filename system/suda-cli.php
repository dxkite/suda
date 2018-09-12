<?php
defined('DATA_DIR') or define('DATA_DIR', '~/.suda');
defined('APP_PUBLIC') or define('APP_PUBLIC', DATA_DIR.'/public');
defined('DEBUG') or define('DEBUG', true);
defined('USER_HOME') or define('USER_HOME', DIRECTORY_SEPARATOR ===  '/' ?$_SERVER["HOME"]:$_SERVER["HOMEDRIVE"].$_SERVER["HOMEPATH"]);

require_once __DIR__.'/__autoload.php';

suda\core\System::init();
suda\core\Config::set('console', true);
suda\core\Autoloader::addIncludePath(__DIR__.'/cli', 'suda\\cli');
suda\cli\CommandRunner::run($argc, $argv);
