<?php
namespace suda\framework\filesystem;

use Iterator;
use RecursiveIteratorIterator;



/**
 * 文件辅助函数
 */
interface FileSystemInterface 
{
    /**
     * 判断文件是否存在
     *
     * @return boolean
     */
    public static function exist(string $path):bool;

    /**
     * 删除文件或者目录
     *
     * @return boolean
     */
    public static function delete(string $filename):bool;

    /**
     * 移动文件
     *
     * @param string $src
     * @param string $dest
     * @return boolean
     */    
    public static function move(string $src, string $dest):bool;

    /**
     * 复制文件
     *
     * @param string $src
     * @param string $dest
     * @return boolean
     */
    public static function copy(string $src, string $dest):bool;

    /**
     * 写入文件
     *
     * @param string $filename
     * @param string $content
     * @param integer $flags
     * @return boolean
     */
    public static function put(string $filename, string $content, int $flags = 0):bool;

    /**
     * 读取文件
     *
     * @param string $name
     * @return string|null
     */
    public static function get(string $filename):?string;


    /**
     * 创建目录
     *
     * @param string $path
     * @param integer $mode
     * @param boolean $recursive
     * @return boolean
     */
    public static function make(string $path, int $mode = 0777, bool $recursive = true):bool;

    /**
     * 读目录下文件
     *
     * @param string $path
     * @param boolean $recursive
     * @param string|null $regex
     * @param boolean $full
     * @return Iterator
     */
    public static function readFiles(string $path, bool $recursive=false, ?string $regex=null, bool $full=true, int $mode = RecursiveIteratorIterator::LEAVES_ONLY) : Iterator;
    
    /**
     * 读目录下文件夹
     *
     * @param string $path
     * @param boolean $recursive
     * @param string|null $regex
     * @param boolean $full
     * @return Iterator
     */
    public static function readDirs(string $path, bool $recursive=false, ?string $regex=null, bool $full=false, int $mode = RecursiveIteratorIterator::LEAVES_ONLY): Iterator;
    
    /**
     * 读目录，包括文件，文件夹
     *
     * @param string $path
     * @param boolean $recursive
     * @param string|null $regex
     * @param boolean $full
     * @return Iterator
     */
    public static function read(string $path, bool $recursive=false, ?string $regex=null, bool $full=true, int $mode = RecursiveIteratorIterator::LEAVES_ONLY): Iterator;

    /**
     * 截断部分目录
     *
     * @param string $path
     * @param string $basepath
     * @return string
     */
    public static function cut(string $path, string $basepath):string;

    /**
     * 复制文件夹
     *
     * @param string $path
     * @param string $toPath
     * @param string|null $regex
     * @param boolean $move
     * @return boolean
     */
    public static function copyDir(string $path, string $toPath, ?string $regex=null, bool $move = false):bool;
    
    /**
     * 移动文件夹
     *
     * @param string $path
     * @param string $toPath
     * @param string|null $regex
     * @return boolean
     */
    public static function moveDir(string $path, string $toPath, ?string $regex=null):bool;
}
