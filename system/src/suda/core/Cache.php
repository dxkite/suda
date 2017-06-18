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
 * Class Cache
 * 文件缓存
 */
class Cache
{
    public static $cache;
    public static $storage=CACHE_DIR.'/data/';
    const CACHE_DEFAULT=86400;

    /**
     * 设置
     * @param string $name 名
     * @param $value 值
     * @param int $expire 过期时间
     * @return bool
     */
    public static function set(string $name, $value, int $expire=null):bool
    {
        $path=self::getPath($name);
        self::$cache[$name]=$value;
        Storage::mkdirs(dirname($path));
        $value=serialize($value);
        if (is_null($expire)) {
            $expire=time()+Cache::CACHE_DEFAULT;
        }
        return file_put_contents($path, $expire.'|'.$value)!==false;
    }

    /**
     * 获取值
     * @param string $name 名
     * @return mixed|null
     */
    public static function get(string $name, $defalut=null)
    {
        // 有值就获取值
        if (isset(self::$cache[$name])) {
            $value=self::$cache[$name];
            return $value;
        }
        // 没值就在cache文件中查找
        $path=self::getPath($name);
        if (Storage::exist($path)) {
            $value=Storage::get($path);
            list($time, $value)=explode('|', $value, 2);
            if (time()<intval($time)) {
                // 未过期则返回
                return unserialize($value);
            } else {
                // 过期则删除
                self::delete($path);
            }
        }
        // 返回默认值
        return $defalut;
    }

    /**
     * 删除值
     * @param string $name 名
     * @return bool
     */
    public static function delete(string $name) :bool
    {
        return Storage::remove(self::nam($name));
    }
    
    
    /**
     * 检测是否存在
     *
     * @param string $name
     * @return bool
     */
    public static function has(string $name):bool
    {
        return self::get($name)!==null;
    }

    /**
     * 垃圾回收
     */
    public static function gc()
    {
        _D()->time('cache gc');
        $files=Storage::readDirFiles(self::$storage, '/^(?!\.)/');
        foreach ($files as $file) {
            if (Config::get('cache', true)) {
                $value=Storage::get($file);
                list($time, $value)=explode('|', $value, 2);
                if (time()>intval($time)) {                 
                    Storage::remove($file);
                }
            } else {
                Storage::remove($file);
            }
        }
        _D()->timeEnd('cache gc');
    }

    private static function getPath(string $name)
    {
        if (strpos($name,'.')) {
            list($main, $sub)=explode('.', $name, 2);
        } else {
            $main=$name;
            $sub=$name;
        }
        $fname=self::$storage.$main.'_'.md5($sub).'.cache';
        return $fname;
    }
}

Hook::listen('system:shutdown::before', 'suda\\core\\Cache::gc');
