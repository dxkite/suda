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

/**
 * 自动加载控制器
 * 控制按照一定的规则自动加载文件或者类
 */
class Autoloader
{
    /**
     * 默认命名空间
     *
     * @var array
     */
    protected static $namespace=[ __NAMESPACE__ ];

    /**
     * 包含路径
     *
     * @var array
     */
    protected static $include_path=[];

    /**
     * 将JAVA，路径分割转换为PHP分割符
     *
     * @param string $name 类名
     * @return string 真实分隔符
     */
    public static function realName(string $name)
    {
        return str_replace(['.','/'], '\\', $name);
    }
    
    public static function realPath(string $name)
    {
        return str_replace(['\\','/'], DIRECTORY_SEPARATOR, $name);
    }

    public static function register()
    {
        spl_autoload_register(array('suda\\core\\Autoloader', 'classLoader'));
        // 载入系统共享库
        self::addIncludePath(dirname(dirname(__DIR__)));
    }

    public static function import(string $filename)
    {
        if (self::filePath($filename)) {
            require_once $filename;
            return $filename;
        } else {
            foreach (self::$include_path as $include_path) {
                if (self::filePath($path=$include_path.DIRECTORY_SEPARATOR.$filename)) {
                    require_once $path;
                    return $path;
                }
            }
        }
    }

    public static function classLoader(string $classname)
    {
        if ($path=static::getClassPath($classname)) {
            if (!class_exists($classname, false)) {
                require_once $path;
            }
        }
    }

    public static function getClassPath(string $className)
    {
        $namepath=self::realPath($className);
        $classname=self::realName($className);
        // 搜索路径
        foreach (self::$include_path as $include_namespace => $include_path) {
            if (is_numeric($include_namespace)) {
                $path= $include_path.DIRECTORY_SEPARATOR.$namepath.'.php';
            } else {
                $nl=strlen($include_namespace);
                if (substr($classname, 0, $nl) == $include_namespace) {
                    $path= $include_path.DIRECTORY_SEPARATOR.static::realPath(substr($classname, $nl)).'.php';
                } else {
                    $path= $include_path.DIRECTORY_SEPARATOR.$namepath.'.php';
                }
            }

            if ($path = self::filePath($path)) {
                return $path;
            } else {
                // 添加命名空间
                foreach (self::$namespace as $namespace) {
                    $path = $include_path.DIRECTORY_SEPARATOR.$namespace.DIRECTORY_SEPARATOR.$namepath.'.php';
                    if ($path = self::filePath($path)) {
                        // 精简类名
                        if (!class_exists($classname, false)) {
                            class_alias($namespace.'\\'.$classname, $classname);
                        }
                        return $path;
                    }
                }
            }
        }
        return false;
    }

    public static function addIncludePath(string $path, string $namespace=null)
    {
        if (static::absolutePath($path)) {
            if (empty($namespace) && !in_array($path, self::$include_path)) {
                self::$include_path[]=$path;
            } elseif (array_search($path, self::$include_path) != $namespace) {
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

    public static function absolutePath(string $path):?string
    {
        $absulotePath = static::parsePath($path);
        return file_exists($absulotePath)?$absulotePath:null;
    }

    public static function parsePath(string $path):string
    {
        // TODO parse ~ as home
        if (IS_CONSOLE && $path[0] === '~') {
            $scheme ='';
            $subpath = USER_HOME.DIRECTORY_SEPARATOR.$path;
        } elseif (strpos($path, '://') !== false) {
            list($scheme, $subpath) = explode('://', $path, 2);
            $scheme.='://';
        } else {
            $scheme ='';
            $subpath = $path;
        }
        $subpath = str_replace(['/', '\\'], '/', $subpath);
        $root = null;
        if (DIRECTORY_SEPARATOR === '/') {
            if ($subpath[0] === '/') {
                $root = '/';
            } else {
                $subpath = getcwd().DIRECTORY_SEPARATOR.$subpath;
            }
            $subpath = substr($subpath, 1);
        } else {
            if (strpos($subpath, ':/') === false) {
                $subpath=str_replace(['/', '\\'], '/', getcwd()).'/'.$subpath;
            }
            list($root, $subpath) = explode(':/', $subpath, 2);
            $root .= ':'.DIRECTORY_SEPARATOR;
        }
        $subpathArr = explode('/', $subpath);
        $absulotePaths = [];
        foreach ($subpathArr as $name) {
            $name = trim($name);
            if ($name === '..') {
                array_pop($absulotePaths);
            } elseif ($name === '.') {
                continue;
            } elseif (strlen($name)) {
                $absulotePaths[]=$name;
            }
        }
        $absulotePath = $scheme.$root.implode(DIRECTORY_SEPARATOR, $absulotePaths);
        return $absulotePath;
    }


    private static function filePath($name)
    {
        $path = self::realPath($name);
        if (file_exists($path) && is_file($path) && $real=static::absolutePath($name)) {
            if (basename($real) === basename($path)) {
                return $real;
            }
        }
        return false;
    }
}
