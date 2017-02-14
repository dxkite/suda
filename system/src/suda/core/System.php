<?php
namespace suda\core;
require_once __DIR__.'/Storage.php';
require_once __DIR__.'/func.php';
class System
{
    protected static $namespace=['suda\\core'];
    protected static $include_path=[];

    public static function init(){
        class_alias('suda\\core\\System','System');
        spl_autoload_register('suda\\core\\System::classLoader');
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
}
