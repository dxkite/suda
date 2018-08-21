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
            self::loadFile($path.'/'.self::$set);
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
            self::loadFile($path.'/'.$locale);
        }
    }
    
    /**
    * 加载语言本地化文件
    */
    public static function loadFile(string $path)
    {
        if ($file=Config::resolve($path)) {
            $localeArray = Config::loadConfig($file);
            return self::include($localeArray);
        }
        return [];
    }

    public static function _(string $string)
    {
        if (isset(self::$langs[$string])) {
            $string=self::$langs[$string];
        }
        $args=func_get_args();
        if (count($args)>1) {
            $args[0]=$string;
            return call_user_func_array('sprintf', $args);
        }
        return $string;
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
