<?php
/**
 * Suda FrameWork
 *
 * An open source application development framework for PHP 7.2.0 or newer
 *
 * Copyright (c)  2017-2018 DXkite
 *
 * @category   PHP FrameWork
 * @package    Suda
 * @copyright  Copyright (c) DXkite
 * @license    MIT
 * @link       https://github.com/DXkite/suda
 * @version    since 1.2.4
 */
namespace suda\core;

use suda\core\Config;
use suda\core\Storage;

/**
* I18N 国际化支持
* 语言控制文件
*/

class Locale
{
    // 默认语言
    const DEFAULT = 'zh-CN';
    private static $langs=[];
    private static $set='zh-CN';
    private static $paths=[];

    /**
     * 包含本地化语言数组
     */
    public static function include(array $locales)
    {
        return self::$langs=array_merge(self::$langs, $locales);
    }

    
    /**
     * 设置语言化文件夹路径
     */
    public static function path(string $path)
    {
        if (!in_array($path, self::$paths)) {
            self::$paths[]=$path;
            self::loadPath($path.'/'.self::$set);
        }
    }

    /**
     * 设置本地化语言类型
     */
    public static function set(string $locale)
    {
        self::$set=$locale;
        self::load($locale);
    }

    /**
     * 加载语言本地化文件
     */
    public static function load(string $locale)
    {
        foreach (self::$paths as $path) {
            self::loadPath($path.'/'.$locale);
        }
    }

    /**
     * 加载路径下的语言文件
     *
     * @param string $path
     * @return void
     */
    public static function loadPath(string $path)
    {
        if (Storage::isFile($path)) {
            self::loadFile($path);
        } elseif (Storage::isDir($path)) {
            $langFiles = Storage::readDirFiles($path);
            foreach ($langFiles as $langFile) {
                self::loadFile($langFile);
            }
        }
    }

    /**
     * 加载语言本地化文件
     *
     * @param string $path
     * @return array
     */
    public static function loadFile(string $path)
    {
        if ($file = Config::resolve($path)) {
            $localeArray = Config::loadConfig($file);
            if (\is_array($localeArray)) {
                return self::include($localeArray);
            }
        }
        return [];
    }

    public static function _(string $string)
    {
        if (isset(self::$langs[$string])) {
            $string=self::$langs[$string];
        }
        $args=func_get_args();
        if (count($args) > 1) {
            if (is_array($args[1])) {
                $param = $args[1];
            } else {
                $param = array_slice($args, 1);
            }
            return self::format($string, $param);
        }
        return $string;
    }

    public static function format(string $string, array $param)
    {
        return preg_replace_callback('/(?<!\$)\$(\{)?(\d+|\w+?\b)(?(1)\})/', function ($match) use ($param) {
            $key = $match[2];
            if (array_key_exists($key, $param)) {
                return $param[$key];
            }
            return $match[0];
        }, $string);
    }

    public static function getLocalePaths()
    {
        return self::$paths;
    }

    public static function getLangs()
    {
        return self::$langs;
    }
}
