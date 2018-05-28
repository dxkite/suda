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

use suda\tool\Json;
use suda\tool\ArrayHelper;

/**
 * 文件配置类
 */
class Config
{
    public static $config=[];

    public static function load(string $file)
    {
        return self::assign(Json::loadFile($file));
    }
    
    public static function assign(array $config)
    {
        return self::$config=array_merge(self::$config, $config);
    }

    public static function get(string $name=null, $default=null)
    {
        if (is_null($name)) {
            return self::$config;
        }
        return ArrayHelper::get(self::$config, $name, $default);
    }

    public static function set(string $name, $value, $combine=null)
    {
        return ArrayHelper::set(self::$config, $name, $value, $combine);
    }

    public static function has(string $name)
    {
        return ArrayHelper::exist(self::$config, $name);
    }
}
