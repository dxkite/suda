<?php
namespace suda\core;
use suda\tool\Json;
use suda\tool\ArrayHelper;

class Config
{
    public static $config=[];

    public static function load(string $file)
    {
        self::$config=array_merge(self::$config,Json::loadFile($file));
    }

    public static function get(string $name=null, $default=null)
    {
        if(is_null( $name)) return self::$config;
        return ArrayHelper::get(self::$config, $name, $default);
    }
    public static function set(string $name, $value, $combine=null)
    {
        return ArrayHelper::set(self::$config, $name, $value, $combine);
    }
}