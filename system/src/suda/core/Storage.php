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
 * @method bool mkdirs(string $dir, int $mode=0777)
 * @method string|null path(string $path)
 * @method string abspath(string $path)
 * @method \Iterator readDirFiles(string $dirs, bool $repeat=false, ?string $preg=null, bool $cut=false)
 * @method \Iterator readDirs(string $dirs, bool $repeat=false, ?string $preg=null)
 * @method \Iterator readPath(string $path, bool $repeat=false, ?string $preg=null)
 * @method mixed  cut(string $path, string $basepath=ROOT_PATH)
 * @method bool delete(string $path)
 * @method bool rmdirs(string $dir)
 * @method bool isEmpty(string $dir)
 * @method bool copydir(string $src, string $dest, ?string $preg=null)
 * @method bool movedir(string $src, string $dest, ?string $preg=null)
 * @method bool copy(string $source, string $dest)
 * @method bool move(string $src, string $dest)
 * @method bool mkdir(string $path, int $mode=0777)
 * @method bool rmdir(string $path)
 * @method bool put(string $name, $content, int $flags = 0)
 * @method string get(string $name)
 * @method bool remove(string $name) 
 * @method bool isFile(string $name)
 * @method bool isDir(string $name)
 * @method bool isReadable(string $name)
 * @method bool isWritable(string $name)
 * @method int size(string $name)
 * @method string type(string $name)
 * @method string|boolean exist(string $name, array $charset=[])
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
