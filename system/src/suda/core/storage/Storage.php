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

namespace suda\core\storage;

/**
 * 存储系统
 */
interface Storage
{

    /**
     * 递归创建文件夹
     *
     * @param string $dir
     * @param integer $mode
     * @return boolean
     */
    public function mkdirs(string $dir, int $mode=0777):bool;

    /**
     * 判断路径是否存在，不存在则创建，创建成功返回路径绝对地址
     *
     * @param string $path
     * @return string|null
     */
    public function path(string $path):?string;
    
    /**
     * 返回路径绝对地址
     *
     * @param string $path
     * @return string|null
     */
    public function abspath(string $path);
    
    /**
     * 读取路径下面所有的文件
     *
     * @param string $dirs
     * @param boolean $repeat
     * @param string $preg
     * @param boolean $cut
     * @return Iterator
     */
    public function readDirFiles(string $dirs, bool $repeat=false, ?string $preg=null, bool $cut=false):\Iterator;
   
    /**
     * 读取路径下面的所有文件或者目录
     *
     * @param string $dirs
     * @param boolean $repeat
     * @param string $preg
     * @return Iterator
     */
    public function readDirs(string $dirs, bool $repeat=false, ?string $preg=null): \Iterator;

    /**
     * 读取路径
     *
     * @param string $path
     * @param boolean $repeat
     * @param string $preg
     * @return \Iterator
     */
    public function readPath(string $path, bool $repeat=false, ?string $preg=null): \Iterator;
    
    /**
     * 截断路径的前部分
     *
     * @param string $path
     * @param string $basepath
     * @return string
     */
    public function cut(string $path, string $basepath=ROOT_PATH);

    /**
     * 删除文件或者目录
     *
     * @param string $path
     * @return boolean
     */
    public function delete(string $path):bool;

    /**
     * 递归删除文件夹
     *
     * @param string $dir
     * @return boolean
     */
    public function rmdirs(string $dir):bool;

    /**
     * 判断文件夹是否为空
     *
     * @param string $dir
     * @return boolean
     */
    public function isEmpty(string $dir):bool;
    /**
     * 复制目录
     *
     * @param string $src
     * @param string $dest
     * @param string $preg
     * @return boolean
     */
    public function copydir(string $src, string $dest, ?string $preg=null):bool;
    /**
     * 移动目录
     *
     * @param string $src
     * @param string $dest
     * @param string $preg
     * @return boolean
     */
    public function movedir(string $src, string $dest, ?string $preg=null):bool;
    /**
     * 复制文件
     *
     * @param string $source
     * @param string $dest
     * @return boolean
     */
    public function copy(string $source, string $dest):bool;
    /**
     * 移动文件
     *
     * @param string $src
     * @param string $dest
     * @return boolean
     */
    public function move(string $src, string $dest):bool;
    /**
     * 创建文件夹
     *
     * @param string $path
     * @param integer $mode
     * @return boolean
     */
    public function mkdir(string $path, int $mode=0777):bool;
    /**
     * 删除文件夹
     *
     * @param string $path
     * @return boolean
     */
    public function rmdir(string $path):bool;
    /**
     * 创建文件
     *
     * @param string $name 文件名
     * @param [type] $content 内容
     * @param integer $flags 标志
     * @return boolean
     */
    public function put(string $name, $content, int $flags = 0):bool;
    /**
     * 获取文件内容
     *
     * @param string $name 文件名
     * @return string
     */
    public function get(string $name):string;
    /**
     * 删除文件
     *
     * @param string $name
     * @return boolean
     */
    public function remove(string $name) : bool;
    /**
     * 判断是否为文件
     *
     * @param string $name
     * @return boolean
     */
    public function isFile(string $name):bool;
    /**
     * 判断是否为目录
     *
     * @param string $name
     * @return boolean
     */
    public function isDir(string $name):bool;
    /**
     * 判断是否可读
     *
     * @param string $name
     * @return boolean
     */
    public function isReadable(string $name):bool;
    /**
     * 判断是否可写
     *
     * @param string $name
     * @return boolean
     */
    public function isWritable(string $name):bool;
    /**
     * 获取文件大小
     *
     * @param string $name
     * @return integer
     */
    public function size(string $name):int;
    /**
     * 取得文件类型
     *
     * @param string $name
     * @return integer
     */
    public function type(string $name):string;
    /**
     * 判断文件是否存在
     *
     * @param string $name
     * @param array $charset
     * @return string|boolean
     */
    public function exist(string $name, array $charset=[]);
    /**
     * 创建一个临时文件
     *
     * @param string $prefix
     * @return string 返回的文件名
     */
    public function temp(string $prefix='dx_');

    public static function getInstance();
}
