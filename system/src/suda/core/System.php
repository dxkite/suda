<?php
namespace suda\core;

defined('D_START') or define('D_START', microtime(true));
defined('D_MEM') or define('D_MEM', memory_get_usage());
defined('ROOT_PATH') or define('ROOT_PATH', dirname(dirname(dirname(dirname(__DIR__)))));
defined('SYS_DIR') or define('SYS_DIR', dirname(dirname(dirname(__DIR__))));
defined('SYS_RES') or define('SYS_RES', SYS_DIR.'/resource');

require_once __DIR__.'/alias.php';

class System
{
    public static function init()
    {
        class_alias('suda\\core\\System', 'System');
        register_shutdown_function('suda\\core\\System::onShutdown');
        set_error_handler('suda\\core\\System::uncaughtError');
        set_exception_handler('suda\\core\\System::uncaughtException');
        Locale::path(SYS_RES.'/locales');
        _D()->trace(_T('system init'));
    }

    public static function onShutdown()
    {
        _D()->trace('include paths:'.json_encode(Autoloader::getIncludePath()));
        _D()->trace(_T('system shutdown'));
        Hook::exec('system:shutdown');
    }

    public static function uncaughtException($exception)
    {
        if (!$exception instanceof Exception){
            $exception=new Exception($exception);
        }
        if (Hook::execIf('system:displayException', [$exception], false)) {
            Debug::displayException($exception);
        }
    }

    // 错误托管
    public static function uncaughtError($errno,$errstr, $errfile, $errline)
    {
        self::uncaughtException(new \ErrorException($errstr, 0, $errno, $errfile, $errline));
    }
}
