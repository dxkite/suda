<?php
/**
 * Suda FrameWork
 *
 * An open source application development framework for PHP 7.0.0 or newer
 * 
 * Copyright (c)  2017 DXkite
 *
 * @category   PHP FrameWork
 * @package    Suda
 * @copyright  Copyright (c) DXkite
 * @license    MIT
 * @link       https://github.com/DXkite/suda
 * @version    since 1.2.4
 */
namespace suda\core;

class Autoloader
{
    protected static $namespace=['suda\\core'];
    protected static $include_path=[];

    public static function realName(string $name)
    {
        return preg_replace('/[.\/\\\\]+/', '\\', $name);
    }
    public static function realPath(string $name)
    {
        return preg_replace('/[\\\\\/]+/', DIRECTORY_SEPARATOR, $name);
    }

    public static function init()
    {
        spl_autoload_register(array('suda\\core\\Autoloader', 'classLoader'));
        self::addIncludePath(dirname(dirname(__DIR__)));
    }

    public static function import(string $filename)
    {
        if (self::file_exists($filename)) {
            require_once $filename;
            return $filename;
        } else {
            foreach (self::$include_path as $include_path) {
                if (self::file_exists($path=$include_path.DIRECTORY_SEPARATOR.$filename)) {
                    require_once $path;
                    return $path;
                }
            }
        }
    }

    public static function classLoader(string $classname)
    {
        $namepath=self::realPath($classname);
        $classname=self::realName($classname);
        // debug_print_backtrace();
        // 搜索路径
        foreach (self::$include_path as $include_namesapce => $include_path) {
            if (!is_numeric($include_namesapce) && preg_match('/^'.preg_quote($include_namesapce).'(.+)$/',$classname,$match)) {
                $path=preg_replace('/[\\\\\\/]+/', DIRECTORY_SEPARATOR, $include_path.DIRECTORY_SEPARATOR.$match[1].'.php');
            }else{
                $path=preg_replace('/[\\\\\\/]+/', DIRECTORY_SEPARATOR, $include_path.DIRECTORY_SEPARATOR.$namepath.'.php');
            }
            if (self::file_exists($path)) {
                if (!class_exists($classname)) {
                    require_once $path;
                }
            } else {
                // 添加命名空间
                foreach (self::$namespace as $namespace) {
                    $path=preg_replace('/[\\\\]+/', DIRECTORY_SEPARATOR, $include_path.DIRECTORY_SEPARATOR.$namespace.DIRECTORY_SEPARATOR.$namepath.'.php');
                    if (self::file_exists($path)) {
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

    public static function addIncludePath(string $path, string $namespace=null)
    {
        if (!in_array($path, self::$include_path) && $path=realpath($path)) {
            if (empty($namespace)) {
                self::$include_path[]=$path;
            } else {
                self::$include_path[$namespace]=$path;
            }
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

    private static function file_exists($name):bool
    {
        if (file_exists($name) && is_file($name) && $real=realpath($name)) {
            if (basename($real) === basename($name)) {
                return true;
            }
        }
        return false;
    }
}
