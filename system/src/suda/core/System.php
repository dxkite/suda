<?php
namespace suda\core;

defined('D_START') or define('D_START', microtime(true));
defined('D_MEM') or define('D_MEM', memory_get_usage());
defined('ROOT_PATH') or define('ROOT_PATH', dirname(dirname(dirname(dirname(__DIR__)))));
defined('SYS_DIR') or define('SYS_DIR', dirname(dirname(dirname(__DIR__))));
defined('SYS_RES') or define('SYS_RES', SYS_DIR.'/resource');

require_once __DIR__.'/func.php';  

class System
{
    public static function init()
    {
        class_alias('suda\\core\\System', 'System');
        register_shutdown_function('suda\\core\\System::onShutdown');
        spl_autoload_register('suda\\core\\System::classLoader');
        set_error_handler('suda\\core\\System::uncaughtError');
        set_exception_handler('suda\\core\\System::uncaughtException');
    }

    public static function onShutdown()
    {
        Hook::exec('system:shutdown');
    }

    public static function uncaughtException($exception)
    {
        if (Hook::execIf('system:uncaughtException', [$exception], false)) {
            Debug::printError($exception->getMessage(), $exception->getCode(), $exception->getFile(), $exception->getLine(), 2);
        }
    }
    // 错误处理函数
    public static function uncaughtError($erron, $error, $file, $line)
    {
        if (Hook::execIf('system:uncaughtError', [$erron, $error, $file, $line], false)) {
            Debug::printError($error, $erron, $file, $line, 2);
        }
    }
}
