<?php
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
            if (file_exists($file=$path.'/'.self::$set.'.json')){
                self::loadFile($file);
            }
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
        // 清空历史
        self::$langs=[];
        _D()->trace(_T('loaded paths %s',implode(';',self::$paths)));
        // 重写
        foreach (self::$paths as $path){
            if (file_exists($file=$path.'/'.$locale.'.json')){
                self::loadFile($file);
            }
        }
    }
    /**
    * 加载语言本地化文件
    */
    public static function loadFile(string $path)
    {
        $jsonfile=file_get_contents($path);
        $json=json_decode($jsonfile, true);
        if (json_last_error()===JSON_ERROR_NONE) {
            return self::include($json);
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
}
