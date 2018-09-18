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

namespace suda\core\cache;

use suda\core\Storage;
use suda\core\Config;

/**
 * 文件缓存
 *
 * 由于访问数据库的效率远远低于访问文件的效率，所以我添加了一个文件缓存类，
 * 你可以把常用的数据和更改很少的数据查询数据库以后缓存到文件里面，用来加快页面加载速度。
 */
class FileCache implements Cache
{
    public static $cache;
    public static $storage=CACHE_DIR.'/data/';
    const CACHE_DEFAULT=86400;
    protected static $intance;

    public function __construct()
    {
        hook()->register('system:shutdown', [__CLASS__,'gc']);
    }

    public static function getInstance()
    {
        if (is_null(self::$intance)) {
            return self::$intance = new self;
        }
        return self::$intance;
    }

    /**
     * 设置
     * @param string $name 名
     * @param $value 值
     * @param int $expire 过期时间
     * @return bool
     */
    public function set(string $name, $value, int $expire=null):bool
    {
        if (self::disable()) {
            return false;
        }
        $path=self::getPath($name);
        self::$cache[$name]=$value;
        Storage::mkdirs(dirname($path));
        $value=serialize($value);
        if (is_null($expire)) {
            $expire=time()+self::CACHE_DEFAULT;
        }
        return file_put_contents($path, $expire.'|'.$value)!==false;
    }

    /**
     * 获取值
     * @param string $name 名
     * @return mixed|null
     */
    public function get(string $name, $defalut=null)
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
    public function delete(string $name) :bool
    {
        return Storage::remove(self::getPath($name));
    }
    
    
    /**
     * 检测是否存在
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name):bool
    {
        return self::get($name)!==null;
    }

    /**
     * 垃圾回收
     */
    public static function gc()
    {
        debug()->time('cache gc');
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
        debug()->timeEnd('cache gc');
    }

    public function clear()
    {
        return Storage::delete(CACHE_DIR);
    }

    public function enable():bool
    {
        return is_writable(CACHE_DIR);
    }

    public function disable():bool
    {
        return !self::enable();
    }

    private static function getPath(string $name)
    {
        if (strpos($name, '.')) {
            list($main, $sub)=explode('.', $name, 2);
        } else {
            $main=$name;
            $sub=$name;
        }
        $fname=self::$storage.md5($main).'-'.md5($sub).'.cache';
        return $fname;
    }
}
