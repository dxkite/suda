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

use suda\core\Config;
use suda\core\Autoloader;

/**
 * 文件存储系统包装类
 * 封装了常用的文件系统函数
 */
class FileStorage implements Storage
{
    protected static $intance;
    public static $charset=['GBK','GB2312','BIG5'];

    public static function getInstance()
    {
        if (is_null(self::$intance)) {
            return self::$intance = new self;
        }
        return self::$intance;
    }

    // 递归创建文件夹
    public function mkdirs(string $dir, int $mode=0777):bool
    {
        $path=Autoloader::parsePath($dir);
        if (!self::isDir($path)) {
            if (!self::mkdirs(dirname($path), $mode)) {
                return false;
            }
            if (!self::mkdir($path, $mode)) {
                return false;
            }
        }
        return true;
    }
    
    public function path(string $path):?string
    {
        $path=Autoloader::parsePath($path);
        return self::mkdirs($path)?$path:null;
    }
    
    public function abspath(string $path)
    {
        if (empty($path)) {
            return false;
        }
        $path=Autoloader::parsePath($path);
        return Autoloader::realPath($path);
    }

    public function readDirFiles(string $parent, bool $repeat=false, ?string $preg=null, bool $full=true) : \Iterator
    {
        $parent=Autoloader::parsePath($parent);
        foreach (self::readPath($parent, $repeat, $preg, $full) as $file) {
            $path = $full?$file:$parent.DIRECTORY_SEPARATOR.$file;
            if (self::isFile($path)) {
                yield $file;
            }
        }
    }

    public function readDirs(string $parent, bool $repeat=false, ?string $preg=null, bool $full=false): \Iterator
    {
        $parent=Autoloader::parsePath($parent);
        foreach (self::readPath($parent, $repeat, $preg, $full) as $dir) {
            $path = $full?$dir:$parent.DIRECTORY_SEPARATOR.$dir;
            if (self::isDir($path)) {
                yield $dir;
            }
        }
    }

    public function readPath(string $parent, bool $repeat=false, ?string $preg=null, bool $full=true): \Iterator
    {
        $parent=Autoloader::parsePath($parent);
        if (self::isDir($parent)) {
            $hd=opendir($parent);
            while ($read=readdir($hd)) {
                if (strcmp($read, '.') !== 0 && strcmp($read, '..') !==0) {
                    $path = $parent.DIRECTORY_SEPARATOR.$read;
                    if ($preg) {
                        if (preg_match($preg, $read)) {
                            if ($full) {
                                yield $path;
                            } else {
                                yield self::cut($path, $parent);
                            }
                        }
                    } else {
                        if ($full) {
                            yield $path;
                        } else {
                            yield self::cut($path, $parent);
                        }
                    }
                    if ($repeat && self::isDir($path)) {
                        foreach (self::readPath($path, $repeat, $preg) as $read) {
                            if ($full) {
                                yield $read;
                            } else {
                                yield self::cut($read, $parent);
                            }
                        }
                    }
                }
            }
            closedir($hd);
        }
    }

    public function cut(string $path, string $basepath=ROOT_PATH)
    {
        return trim(preg_replace('/[\\\\\\/]+/', DIRECTORY_SEPARATOR, preg_replace('/^'.preg_quote($basepath, '/').'/', '', $path)), '\\/');
    }

    public function delete(string $path):bool
    {
        if (empty($path)) {
            return false;
        }
        if (self::isFile($path)) {
            self::remove($path);
        } elseif (self::isDir($path)) {
            self::rmdirs($path);
        }
        return self::exist($path) === false;
    }

    /**
     * 递归删除文件夹
     *
     * @param string $parent
     * @return boolean
     */
    public function rmdirs(string $parent):bool
    {
        if (self::isDir($parent)) {
            foreach (self::readPath($parent) as $path) {
                if (self::isFile($path)) {
                    $errorhandler = function ($erron, $error, $file, $line) {
                        Debug::warning($error);
                    };
                    set_error_handler($errorhandler);
                    unlink($path);
                    restore_error_handler();
                }
                if (self::isEmpty($path)) {
                    rmdir($path);
                } else {
                    self::rmdirs($path);
                }
            }
            rmdir($parent);
            return true;
        }
        return false;
    }

    public function isEmpty(string $dirOpen):bool
    {
        while (self::readDirs($dirOpen)) {
            return false;
        }
        return true;
    }

    public function copydir(string $src, string $dest, ?string $preg=null):bool
    {
        if ($path = self::path($dest)) {
            foreach (self::readPath($src, false, $preg, false) as $read) {
                if (self::isDir($src.DIRECTORY_SEPARATOR.$read)) {
                    self::copydir($src.DIRECTORY_SEPARATOR.$read, $dest.DIRECTORY_SEPARATOR.$read, $preg);
                } else {
                    self::copy($src.DIRECTORY_SEPARATOR.$read, $dest.DIRECTORY_SEPARATOR.$read);
                }
            }
            return true;
        } else {
            return false;
        }
    }
    
    public function movedir(string $src, string $dest, ?string $preg=null):bool
    {
        if ($path = self::path($dest)) {
            foreach (self::readPath($src, false, $preg, false) as $read) {
                if (self::isDir($src.DIRECTORY_SEPARATOR.$read)) {
                    self::movedir($src.DIRECTORY_SEPARATOR.$read, $dest.DIRECTORY_SEPARATOR.$read, $preg);
                    self::rmdir($src.DIRECTORY_SEPARATOR.$read);
                } else {
                    self::move($src.DIRECTORY_SEPARATOR.$read, $dest.DIRECTORY_SEPARATOR.$read);
                }
            }
            self::rmdir($src);
            return true;
        } else {
            return false;
        }
    }
    
    public function copy(string $src, string $dest):bool
    {
        $src=Autoloader::parsePath($src);
        $dest=Autoloader::parsePath($dest);
        if (!is_writable(dirname($dest))) {
            return false;
        }
        if (self::exist($src)) {
            return copy($src, $dest);
        }
        return false;
    }

    public function move(string $src, string $dest):bool
    {
        $src=Autoloader::parsePath($src);
        $dest=Autoloader::parsePath($dest);
        if (!is_writable(dirname($dest))) {
            return false;
        }
        if (self::exist($src)) {
            return rename($src, $dest);
        }
        return false;
    }

    // 创建文件夹
    public function mkdir(string $path, int $mode=0777):bool
    {
        $path=Autoloader::parsePath($path);
        if (!self::isDir($path) && is_writable(dirname($path))) {
            return mkdir($path, $mode);
        }
        return false;
    }

    // 删除文件夹
    public function rmdir(string $path):bool
    {
        $path=Autoloader::parsePath($path);
        if (!is_writable($path)) {
            return false;
        }
        return rmdir($path);
    }

    public function put(string $name, $content, int $flags = 0):bool
    {
        $name=Autoloader::parsePath($name);
        $dirname=dirname($name);
        if (self::isDir($dirname) && is_writable($dirname)) {
            return file_put_contents($name, $content, $flags);
        }
        return false;
    }

    public function get(string $name):string
    {
        $name=Autoloader::parsePath($name);
        if ($file=self::exist($name)) {
            if (is_string($file)) {
                $name=$file;
            }
            if (is_readable($name)) {
                return file_get_contents($name);
            }
        }
        return '';
    }

    /**
     * @param string $name
     * @return bool
     */
    public function remove(string $name) : bool
    {
        $name=Autoloader::parsePath($name);
        if ($file=self::exist($name)) {
            if (is_string($file)) {
                $name=$file;
            }
            if (!is_writable($name)) {
                return false;
            }
            return unlink($name);
        }
        return true;
    }
    
    public function isFile(string $name):bool
    {
        $name=Autoloader::parsePath($name);
        return is_file($name);
    }

    public function isDir(string $name):bool
    {
        $name=Autoloader::parsePath($name);
        return is_dir($name);
    }

    public function isReadable(string $name):bool
    {
        $name=Autoloader::parsePath($name);
        return is_readable($name);
    }

    public function isWritable(string $name):bool
    {
        $name=Autoloader::parsePath($name);
        return is_writable($name);
    }
    
    public function size(string $name):int
    {
        $name=Autoloader::parsePath($name);
        if ($file=self::exist($name)) {
            if (is_string($file)) {
                $name=$file;
            }
            return filesize($name);
        }
        return 0;
    }

    public static function download(string $url, string $save):int
    {
        $save=Autoloader::parsePath($save);
        return self::put($save, self::curl($url));
    }
    
    public static function curl(string $url, int $timeout=3)
    {
        $ch=curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $file=curl_exec($ch);
        curl_close($ch);
        return $file;
    }

    public function type(string $name):string
    {
        $name=Autoloader::parsePath($name);
        if ($file=self::exist($name)) {
            if (is_string($file)) {
                $name=$file;
            }
            return filetype($name);
        }
        return '';
    }

    public function exist(string $name, array $charset=[])
    {
        $path=Autoloader::parsePath($name);
        // UTF-8 格式文件路径
        if (self::existCase($path)) {
            return true;
        }
        // Windows 文件中文编码
        $charset=array_merge(self::$charset, $charset);
        foreach ($charset as $code) {
            $file = iconv('UTF-8', $code, $path);
            if ($file && self::existCase($file)) {
                return $file;
            }
        }
        return false;
    }

    // 判断文件或者目录存在
    private function existCase($name):bool
    {
        $name=Autoloader::parsePath($name);
        if (file_exists($name) && $real=Autoloader::realPath($name)) {
            if (basename($real) === basename($name)) {
                return true;
            }
        }
        return false;
    }

    public function temp(string $prefix='dx_')
    {
        return tempnam(sys_get_temp_dir(), $prefix);
    }
    
    public function touchIndex(string $dest, string $content = 'dxkite-suda@'.SUDA_VERSION)
    {
        $dest=Autoloader::parsePath($dest);
        $dest=self::path($dest);
        if ($dest) {
            $index = $dest.'/'.conf('default-index', 'index.html');
            if (!self::exist($index)) {
                file_put_contents($index, $content);
            }
            $dirs = self::readDirs($dest, true, null, true);
            foreach ($dirs as $path) {
                $index = $path.'/'.conf('default-index', 'index.html');
                if (!self::exist($index)) {
                    file_put_contents($index, $content);
                }
            }
            return true;
        }
        return false;
    }
}
