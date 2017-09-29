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

use suda\tool\CookieSetter as Setter;

/**
 * Class Cookie
 * 用于获取Cookie
 */
class Cookie
{
    public static $values=[];
    /**
     * @param string $name Cookie名
     * @param string $value 设置的值
     * @param int $expire  到期时间
     * @return CookieSetter 设置对象
     */
    public static function set(string $name, string $value, int $expire=null) : Setter
    {
        if (is_null($expire)) {
            self::$values[$name]=(new Setter($name, $value, 0))->session();
        } else {
            self::$values[$name]=new Setter($name, $value, $expire);
        }
        return self::$values[$name];
    }
    
    public static function unset(string $name)
    {
        self::set($name, '', 0);
    }
    
    public static function has(string $name)
    {
        return isset(self::$values[$name]) || isset($_COOKIE[$name]);
    }
    /**
     * 获取Cookie的值
     * @param string $name
     * @return string cookie的值
     */
    public static function get(string $name, $default='') : string
    {
        return isset(self::$values[$name])?self::$values[$name]->get():(isset($_COOKIE[$name])?$_COOKIE[$name]:$default);
    }

    /**
     * 发送Cookie至浏览器
     */
    public static function sendCookies()
    {
        foreach (self::$values as $setter) {
            $setter->set();
        }
    }

    /**
    * 从字符串设置cookie
    */
    public static function parseFromString(string $cookie_str)
    {
        $sets=explode(';', $cookie_str);
        foreach ($sets as $str) {
            list($key, $value)=explode('=', $str, 2);
            $_COOKIE[trim($key)]=trim($value);
        }
    }
}

register_shutdown_function(['suda\\core\\Cookie', 'sendCookies']);
