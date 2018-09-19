<?php
/**
 * Suda FrameWork
 *
 * An open source application development framework for PHP 7.0.0 or newer
 *
 * Copyright (c)  2018 DXkite
 *
 * @category   PHP FrameWork
 * @package    Suda
 * @copyright  Copyright (c) DXkite
 * @license    MIT
 * @link       https://github.com/DXkite/suda
 * @version    since 2.0
 */

namespace suda\core;

/**
 * 文件存储系统包装类，封装了常用的文件系统函数
 */
class Storage
{
    protected static $storage;

    public static function getInstance(string $type = 'File')
    {
        if (class_exists($class=__NAMESPACE__.'\\storage\\'.ucfirst($type).'Storage')) {
            static::$storage[$type]=$class::getInstance();
            return static::$storage[$type];
        } else {
            throw new Exception(__('unsupport type of storage:$0', $type));
        }
    }
 
    public static function __callStatic(string $method, $args)
    {
        return call_user_func_array([self::getInstance(),$method], $args);
    }
}
