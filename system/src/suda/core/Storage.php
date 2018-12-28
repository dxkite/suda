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
 * @version    since 2.0
 */

namespace suda\core;

/**
 * 文件存储系统包装类，封装了常用的文件系统函数
 * 
 * @method static bool mkdirs(string $dir, int $mode=0777)
 * @method static string|null path(string $path)
 * @method static string abspath(string $path)
 * @method static \Iterator readDirFiles(string $dirs, bool $repeat=false, ?string $preg=null, bool $cut=false)
 * @method static \Iterator readDirs(string $dirs, bool $repeat=false, ?string $preg=null)
 * @method static \Iterator readPath(string $path, bool $repeat=false, ?string $preg=null)
 * @method static mixed  cut(string $path, string $basepath=ROOT_PATH)
 * @method static bool delete(string $path)
 * @method static bool rmdirs(string $dir)
 * @method static bool isEmpty(string $dir)
 * @method static bool copydir(string $src, string $dest, ?string $preg=null)
 * @method static bool movedir(string $src, string $dest, ?string $preg=null)
 * @method static bool copy(string $source, string $dest)
 * @method static bool move(string $src, string $dest)
 * @method static bool mkdir(string $path, int $mode=0777)
 * @method static bool rmdir(string $path)
 * @method static bool put(string $name, $content, int $flags = 0)
 * @method static string get(string $name)
 * @method static bool remove(string $name) 
 * @method static bool isFile(string $name)
 * @method static bool isDir(string $name)
 * @method static bool isReadable(string $name)
 * @method static bool isWritable(string $name)
 * @method static int size(string $name)
 * @method static string type(string $name)
 * @method static string|boolean exist(string $name, array $charset=[])
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
