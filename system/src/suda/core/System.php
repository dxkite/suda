<?php
namespace suda\core;
defined('D_START') or define('D_START', microtime(true));
defined('D_MEM') or define('D_MEM', memory_get_usage());
require_once __DIR__.'/Storage.php';
require_once __DIR__.'/func.php';
class System
{
    protected static $namespace=['suda\\core'];
    protected static $include_path=[];

    public static function init()
    {
        class_alias('suda\\core\\System', 'System');
        spl_autoload_register('suda\\core\\System::classLoader');
        register_shutdown_function('suda\\core\\System::onShutdown');
        set_error_handler('suda\\core\\System::uncaughtError');
        set_exception_handler('suda\\core\\System::uncaughtException');
    }
    public static function classLoader(string $classname)
    {
        $classfile=preg_replace('/[\\\\]+/', DIRECTORY_SEPARATOR, $classname);
        // 搜索路径
        foreach (self::$include_path as $include_path) {
            if (Storage::exist($path=$include_path.DIRECTORY_SEPARATOR.$classname.'.php')) {
                require_once $path;
            } else {
                // 添加命名空间
                foreach (self::$namespace as $namespace) {
                    if (Storage::exist($path=$include_path.DIRECTORY_SEPARATOR.$namespace.DIRECTORY_SEPARATOR.$classname.'.php')) {
                        class_alias($namespace.'\\'.$classname, $classname);
                        require_once $path;
                    }
                }
            }
        }
    }

    
    public static function addIncludePath(string $path)
    {
        self::$include_path[]=$path;
    }

    public static function getIncludePath()
    {
        return self::$include_path;
    }
    public static function setNamespace(string $namespace){
        self::$namespace[]=$namespace;
    }
    public static function onShutdown()
    {
        Hook::exec('system:shutdown');
    }

    public static function uncaughtException($exception)
    {
        Debug::printError($exception->getMessage(), $exception->getCode(), $exception->getFile(), $exception->getLine(), 2);
    }
    // 错误处理函数
    public static function uncaughtError($erron, $error, $file, $line)
    {
        Debug::printError($error, $erron, $file, $line, 2);
    }
}
