<?php
namespace suda\core;

class Autoloader
{
    protected static $namespace=['suda\\core'];
    protected static $include_path=[];

    public static function realName(string $name) {
        return preg_replace('/[.\/]+/','\\',$name);
    }

    public static function init()
    {
        spl_autoload_register(array('suda\\core\\Autoloader', 'classLoader'));
        self::addIncludePath(dirname(dirname(__DIR__)));
    }

    public static function import(string $filename){
        if (file_exists($filename)){
            require_once $filename;
            return $filename;
        }else{
            foreach (self::$include_path as $include_path) {
                if (file_exists($path=$include_path.DIRECTORY_SEPARATOR.$filename)){
                    require_once $path;
                    return $path;
                }
            }
        }
    }

    public static function classLoader(string $classname)
    {
        $classname=self::realName($classname);
        // 搜索路径
        foreach (self::$include_path as $include_path) {
            $path=preg_replace('/[\\\\\\/]+/', DIRECTORY_SEPARATOR, $include_path.DIRECTORY_SEPARATOR.$classname.'.php');
            if (file_exists($path)) {
                if (!class_exists($classname)) {
                    require_once $path;
                }
            } else {
                // 添加命名空间
                foreach (self::$namespace as $namespace) {
                    $path=preg_replace('/[\\\\]+/', DIRECTORY_SEPARATOR, $include_path.DIRECTORY_SEPARATOR.$namespace.DIRECTORY_SEPARATOR.$classname.'.php');
                    if (file_exists($path)) {
                        // 最简类名
                        if (!class_exists($classname)) {
                            class_alias($namespace.'\\'.$classname, $classname);
                        }
                        require_once $path;
                    }
                }
            }
        }
    }

    public static function addIncludePath(string $path)
    {
        if (!in_array($path, self::$include_path) && $path=realpath($path)) {
            self::$include_path[]=$path;
        }
    }

    public static function getIncludePath()
    {
        return self::$include_path;
    }

    public static function getNamespace()
    {
        return self::$namespace;
    }
    public static function setNamespace(string $namespace)
    {
        if (!in_array($namespace, self::$namespace)) {
            self::$namespace[]=$namespace;
        }
    }
}
