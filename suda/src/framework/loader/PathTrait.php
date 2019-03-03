<?php
namespace suda\framework\loader;

/**
 * 自动加载路径处理
 *
 */
trait PathTrait
{    
    public static function formatSeparator(string $path):string
    {
        return str_replace(['\\','/'], DIRECTORY_SEPARATOR, $path);
    }

    public static function toAbsolutePath(string $path, string $separator = DIRECTORY_SEPARATOR):string{
        list($scheme, $path) = static::parsePathSchemeSubpath($path);
        list($root, $path) = static::parsePathRootSubpath($path);
        $path = static::parsePathRelativePath($path, $separator);
        return $scheme.$root.$path;
    }

    protected static function parsePathRootSubpath(string $path):array {
        $subpath = str_replace(['/', '\\'], '/', $path);
        $root = null;
        if (DIRECTORY_SEPARATOR === '/') {
            if (strpos($path, '/') !== 0) {
                $subpath = getcwd().DIRECTORY_SEPARATOR.$subpath;
            }
            $root = '/';
            $subpath = substr($subpath, 1);
        } else {
            if (strpos($subpath, ':/') === false) {
                $subpath=str_replace(['/', '\\'], '/', getcwd()).'/'.$subpath;
            }
            list($root, $subpath) = explode(':/', $subpath, 2);
            $root .= ':'.DIRECTORY_SEPARATOR;
        }
        return [$root, $subpath];
    }

    protected static function parsePathRelativePath(string $path, string $separator = DIRECTORY_SEPARATOR):string {
        $subpathArr = explode('/', $path);
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
        return implode($separator, $absulotePaths);
    }

    protected static function parsePathSchemeSubpath(string $path):array
    {
        if (static::getHomePath() !== null && strpos($path, '~') === 0) {
            $scheme ='';
            $subpath = static::getHomePath() .DIRECTORY_SEPARATOR.substr($path, 1);
        } elseif (strpos($path, '://') !== false) {
            list($scheme, $subpath) = explode('://', $path, 2);
            $scheme.='://';
        } else {
            $scheme ='';
            $subpath = $path;
        }
        return [$scheme, $subpath];
    }


    public static function getHomePath():?string {
        if (defined('USER_HOME_PATH')) {
            return constant('USER_HOME_PATH');
        }
        return null;
    }
}
