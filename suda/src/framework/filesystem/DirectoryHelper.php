<?php
namespace suda\framework\filesystem;

use RecursiveIteratorIterator;
use suda\component\loader\Path;
use suda\component\loader\PathTrait;
use suda\framework\filesystem\FileHelper;

/**
 * 文件夹辅助
 */
trait DirectoryHelper
{
    /**
     * 递归创建目录
     *
     * @param string $path
     * @param integer $mode
     * @return boolean
     */
    public static function makes(string $path, int $mode = 0777):bool
    {
        if (!is_dir($path)) {
            if (!static::makes(dirname($path), $mode)) {
                return false;
            }
            if (!static::make($path, $mode)) {
                return false;
            }
        }
        return true;
    }

    /**
     * 创建目录
     *
     * @param string $path
     * @param integer $mode
     * @return boolean
     */
    public static function make(string $path, int $mode):bool
    {
        if (\is_dir(\dirname($path)) && is_writable(dirname($path))) {
            $mk = mkdir($path, $mode);
            if ($mk) {
                chmod($path, $mode);
            }
            return $mk;
        }
        return false;
    }

    /**
     * 删除空目录
     *
     * @param string $path
     * @return boolean
     */
    public static function rm(string $path):bool
    {
        if (!is_writable($path)) {
            return false;
        }
        return rmdir($path);
    }

    /**
     * 删除非空目录
     *
     * @param string $path
     * @param string|null $regex
     * @return boolean
     */
    public static function rmdirs(string $path, ?string $regex = null):bool
    {
        foreach (static::read($path, false, $regex, true) as $subpath) {
            if (\is_dir($subpath)) {
                static::rmdirs($subpath, $regex);
            } elseif (is_file($subpath)) {
                FileHelper::delete($subpath);
            }
        }
        static::rm($path);
        return true;
    }


    /**
     * 读目录下文件
     *
     * @param string $path
     * @param boolean $recursive
     * @param string|null $regex
     * @param boolean $full
     * @return \Iterator
     */
    public static function readFiles(string $path, bool $recursive=false, ?string $regex=null, bool $full=true, int $mode = RecursiveIteratorIterator::LEAVES_ONLY) : \Iterator
    {
        $parent = Path::format($path);
        foreach (static::read($path, $recursive, $regex, false, $mode) as $subpath) {
            $path = $full?$subpath:$parent.DIRECTORY_SEPARATOR.$subpath;
            if (is_file($path)) {
                yield $subpath;
            }
        }
    }

    /**
     * 读目录下文件夹
     *
     * @param string $path
     * @param boolean $recursive
     * @param string|null $regex
     * @param boolean $full
     * @return \Iterator
     */
    public static function readDirs(string $path, bool $recursive=false, ?string $regex=null, bool $full=false, int $mode = RecursiveIteratorIterator::LEAVES_ONLY): \Iterator
    {
        $parent = Path::format($path);
        foreach (static::read($path, $recursive, $regex, false, $mode) as $subpath) {
            $path = $full?$subpath:$parent.DIRECTORY_SEPARATOR.$subpath;
            if (is_dir($path)) {
                yield $subpath;
            }
        }
    }

    /**
     * 读目录，包括文件，文件夹
     *
     * @param string $path
     * @param boolean $recursive
     * @param string|null $regex
     * @param boolean $full
     * @return \Iterator
     */
    public static function read(string $path, bool $recursive=false, ?string $regex=null, bool $full=true, int $mode = RecursiveIteratorIterator::LEAVES_ONLY): \Iterator
    {
        $directory = Path::format($path);
        if ($directory && \is_dir($directory)) {
            $it = static::buildIterator($directory, $recursive, $regex, $mode);
            foreach ($it as $key => $item) {
                if ($full) {
                    yield $key;
                } else {
                    yield static::cut($key, $directory);
                }
            }
        }
    }
    

    protected static function buildIterator(string $directory, bool $recursive=false, ?string $regex = null, int $mode): \Iterator
    {
        $it = new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS);
        if ($regex !== null) {
            $it = new \RecursiveRegexIterator($it, $regex);
        }
        if ($recursive) {
            $it = new \RecursiveIteratorIterator($it, $mode);
        }
        return $it;
    }

    /**
     * 截断部分目录
     *
     * @param string $path
     * @param string $basepath
     * @return string
     */
    public static function cut(string $path, string $basepath):string
    {
        $path =  PathTrait::toAbsolutePath($path);
        $basepath = PathTrait::toAbsolutePath($basepath);
        if (strpos($path, $basepath.DIRECTORY_SEPARATOR) === 0) {
            return substr($path, \strlen($basepath) + 1);
        }
        return $path;
    }

    /**
     * 复制文件
     *
     * @param string $path
     * @param string $toPath
     * @param string|null $regex
     * @param boolean $move
     * @return boolean
     */
    public static function copy(string $path, string $toPath, ?string $regex=null, bool $move = false):bool
    {
        $directory = Path::format($path);
        static::makes($toPath);
        if ($directory && \is_writable($toPath)) {
            foreach (static::read($directory, true, $regex, false, RecursiveIteratorIterator::CHILD_FIRST) as $subpath) {
                $srcpath = $directory.DIRECTORY_SEPARATOR.$subpath;
                $destpath = $toPath.DIRECTORY_SEPARATOR.$subpath;
                static::proccessPath($srcpath, $destpath, $move);
            }
            return true;
        }
        return false;
    }

    protected static function proccessPath(string $srcpath, string $destpath, bool $move)
    {
        if (is_dir($srcpath)) {
            static::makes($destpath);
            if ($move) {
                static::rm($srcpath);
            }
        } else {
            static::makes(dirname($destpath));
            FileHelper::copy($srcpath, $destpath);
            if ($move) {
                FileHelper::delete($srcpath);
            }
        }
    }

    /**
     * 移动文件
     *
     * @param string $path
     * @param string $toPath
     * @param string|null $regex
     * @return boolean
     */
    public static function move(string $path, string $toPath, ?string $regex=null):bool
    {
        return static::copy($path, $toPath, $regex, true);
    }
}