<?php





defined('SUDA_SYSTEM') or define('SUDA_SYSTEM', dirname(dirname(__DIR__)));
defined('SUDA_RESOURCE') or define('SUDA_RESOURCE', SUDA_SYSTEM.'/resource');
defined('SUDA_START_TIME') or define('SUDA_START_TIME', microtime(true));
defined('SUDA_START_MEMORY') or define('SUDA_START_MEMORY', memory_get_usage());

// HOME PAHT GET
if (!defined('USER_HOME_PATH')) {
    // for linux
    if (array_key_exists('HOME', $_SERVER)) {
        define('USER_HOME_PATH', $_SERVER['HOME']);
    }
    // for windows
    elseif (array_key_exists('HOMEDRIVE', $_SERVER) && array_key_exists('HOMEPATH', $_SERVER)) {
        define('USER_HOME_PATH', $_SERVER['HOMEDRIVE'].$_SERVER['HOMEPATH']);
    }
    // for unknown
    else {
        define('USER_HOME_PATH', getcwd());
    }
}

require_once SUDA_SYSTEM .'/src/framework/loader/Loader.php';


