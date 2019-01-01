<?php

defined('DEBUG') or define('DEBUG', true);

// get user home
if (!defined('USER_HOME')) {
    // for linux
    if (array_key_exists('HOME', $_SERVER)) {
        define('USER_HOME', $_SERVER["HOME"]);
    }
    // for windows
    elseif (array_key_exists('HOMEDRIVE', $_SERVER) && array_key_exists('HOMEPATH', $_SERVER)) {
        define('USER_HOME', $_SERVER["HOMEDRIVE"].$_SERVER["HOMEPATH"]);
    }
    // for unknown
    else {
        define('USER_HOME', getcwd());
    }
}

require_once __DIR__.'/__autoload.php';

defined('DATA_DIR') or define('DATA_DIR', suda\core\Autoloader::parsePath('~/.suda'));
defined('APP_PUBLIC') or define('APP_PUBLIC', DATA_DIR.'/public');

suda\core\System::init();
suda\core\Config::set('console', true);
