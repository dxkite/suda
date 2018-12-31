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

/**
 * 缓存系统
 *
 * 由于访问数据库的效率远远低于访问文件的效率，所以我添加了一个文件缓存类，
 * 你可以把常用的数据和更改很少的数据查询数据库以后缓存到文件里面，用来加快页面加载速度。
 */
class Cache
{
    protected static $cache;

    public static function getInstance(string $type = 'File')
    {
        if (class_exists($class=__NAMESPACE__.'\\cache\\'.ucfirst($type).'Cache')) {
            static::$cache[$type]=$class::getInstance();
            return static::$cache[$type];
        } else {
            throw new \Exception(__('unsupport type of cache:$0', $type));
        }
    }
 
    public static function __callStatic(string $method, $args)
    {
        return call_user_func_array([self::getInstance(conf('cache.type', 'File')),$method], $args);
    }
}
