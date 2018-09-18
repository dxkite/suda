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
        $path=self::osPath($dir);
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
    
    public function path(string $path)
    {
        $path=self::osPath($path);
        self::mkdirs($path);
        return Autoloader::absolutePath($path);
    }
    
    public function abspath(string $path)
    {
        if (empty($path)) {
            return false;
        }
        $path=self::osPath($path);
        return Autoloader::absolutePath($path);
    }

    public function readDirFiles(string $dirs, bool $repeat=false, string $preg='/^.+$/', bool $cut=false):array
    {
        $dirs=self::abspath($dirs);
        $file_totu=[];
        if ($dirs && self::isDir($dirs)) {
            $hd=opendir($dirs);
            while ($file=readdir($hd)) {
                if (strcmp($file, '.') !== 0 && strcmp($file, '..') !==0) {
                    $path=$dirs.'/'.$file;
                    if (self::exist($path) && preg_match($preg, $file)) {
                        $file_totu[]=$path;
                    } elseif ($repeat && self::isDir($path)) {
                        foreach (self::readDirFiles($path, $repeat, $preg) as $files) {
                            $file_totu[]=$files;
                        }
                    }
                }
            }
            closedir($hd);
        }
        if ($cut) {
            $cutfile=[];
            foreach ($file_totu as $file) {
                $cutfile[]=self::cut($file, $dirs);
            }
            return $cutfile;
        }
        return $file_totu;
    }

    public function cut(string $path, string $basepath=ROOT_PATH)
    {
        return trim(preg_replace('/[\\\\\\/]+/', DIRECTORY_SEPARATOR, preg_replace('/^'.preg_quote($basepath, '/').'/', '', $path)), '\\/');
    }

    public function readDirs(string $dirs, bool $repeat=false, string $preg='/^.+$/'):array
    {
        $dirs=self::osPath($dirs);
        $reads=[];
        if (self::isDir($dirs)) {
            $hd=opendir($dirs);
            while ($read=readdir($hd)) {
                if (strcmp($read, '.') !== 0 && strcmp($read, '..') !==0) {
                    $path=$dirs.'/'.$read;
                    if (self::isDir($path) && preg_match($preg, $read)) {
                        $reads[]=$read;
                        if ($repeat) {
                            foreach (self::readDirs($path, $repeat, $preg) as $read) {
                                $reads[]=$read;
                            }
                        }
                    }
                }
            }
            closedir($hd);
        }
        return $reads;
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

    // 递归删除文件夹
    public function rmdirs(string $dir):bool
    {
        $dir=self::abspath($dir);
        if ($dir  && $handle=opendir($dir)) {
            while (false!== ($item=readdir($handle))) {
                if ($item!= '.' && $item != '..') {
                    if (self::isDir($next= $dir.'/'.$item)) {
                        self::rmdirs($next);
                    } elseif (file_exists($file=$dir.'/'.$item)) { // Non-Thread-Safe
                        $errorhandler=function ($erron, $error, $file, $line) {
                            Debug::warning($error);
                        };
                        set_error_handler($errorhandler);
                        unlink($file);
                        restore_error_handler();
                    }
                }
            }
            if (self::isEmpty($dir)) {
                rmdir($dir);
            }
            closedir($handle);
        }
        return true;
    }

    public function isEmpty(string $dirOpen):bool
    {
        if ($dirOpen && self::abspath($dirOpen)) {
            $handle=opendir($dirOpen);
            while (false!== ($item=readdir($handle))) {
                if ($item!= '.' && $item != '..') {
                    return false;
                }
            }
            closedir($handle);
        }
        return true;
    }

    public function copydir(string $src, string $dest, string $preg='/^.+$/'):bool
    {
        $src=self::osPath($src);
        $dest=self::osPath($dest);
        self::mkdirs($dest);
        if (is_writable($dest)) {
            $dest=self::path($dest);
        } else {
            return false;
        }
        $hd=opendir($src);
        while ($read=readdir($hd)) {
            if (strcmp($read, '.') !== 0 && strcmp($read, '..') !==0 && preg_match($preg, $read)) {
                if (self::isDir($src.'/'.$read)) {
                    self::copydir($src.'/'.$read, $dest.'/'.$read, $preg);
                } else {
                    self::copy($src.'/'.$read, $dest.'/'.$read);
                }
            }
        }
        closedir($hd);
        return true;
    }
    
    public function movedir(string $src, string $dest, string $preg='/^.+$/'):bool
    {
        $src=self::osPath($src);
        $dest=self::osPath($dest);
        self::mkdirs($dest);
        if (is_writable($dest)) {
            $dest=self::path($dest);
        } else {
            return false;
        }
        $hd=opendir($src);
        while ($read=readdir($hd)) {
            if (strcmp($read, '.') !== 0 && strcmp($read, '..') !==0 && preg_match($preg, $read)) {
                if (self::isDir($src.'/'.$read)) {
                    self::movedir($src.'/'.$read, $dest.'/'.$read);
                } else {
                    self::move($src.'/'.$read, $dest.'/'.$read);
                }
            }
        }
        closedir($hd);
        return true;
    }
    
    public function copy(string $source, string $dest):bool
    {
        $source=self::osPath($source);
        $dest=self::osPath($dest);
        if (!is_writable(dirname($dest))) {
            return false;
        }
        if (self::exist($source)) {
            return copy($source, $dest);
        }
        return false;
    }

    public function move(string $src, string $dest):bool
    {
        $src=self::osPath($src);
        $dest=self::osPath($dest);
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
        $path=self::osPath($path);
        if (!self::isDir($path) && is_writable(dirname($path))) {
            return mkdir($path, $mode);
        }
        return false;
    }

    // 删除文件夹
    public function rmdir(string $path):bool
    {
        $path=self::osPath($path);
        if (!is_writable($path)) {
            return false;
        }
        return rmdir($path);
    }

    public function put(string $name, $content, int $flags = 0):bool
    {
        $name=self::osPath($name);
        $dirname=dirname($name);
        if (self::isDir($dirname) && is_writable($dirname)) {
            return file_put_contents($name, $content, $flags);
        }
        return false;
    }

    public function get(string $name):string
    {
        $name=self::osPath($name);
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
        $name=self::osPath($name);
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
        $name=self::osPath($name);
        return is_file($name);
    }

    public function isDir(string $name):bool
    {
        $name=self::osPath($name);
        return is_dir($name);
    }

    public function isReadable(string $name):bool
    {
        $name=self::osPath($name);
        return is_readable($name);
    }

    public function isWritable(string $name):bool
    {
        $name=self::osPath($name);
        return is_writable($name);
    }
    
    public function size(string $name):int
    {
        $name=self::osPath($name);
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
        $save=self::osPath($save);
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
        $name=self::osPath($name);
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
        $path=self::osPath($name);
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
        $name=self::osPath($name);
        if (file_exists($name) && $real=Autoloader::absolutePath($name)) {
            if (basename($real) === basename($name)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 修正路径分割符
     *
     * @param string $path
     * @return void
     */
    private function osPath(string $path)
    {
        return Autoloader::parsePath($path);
    }

    public function temp(string $prefix='dx_')
    {
        return tempnam(sys_get_temp_dir(), $prefix);
    }
    
    public function touchIndex(string $dest, string $content = 'dxkite-suda@'.SUDA_VERSION)
    {
        $dest=self::osPath($dest);
        if (is_writable($dest)) {
            $dest=self::path($dest);
            $index = $dest.'/'.conf('defaultIndex', 'index.html');
            if (!self::exist($index)) {
                file_put_contents($index, $content);
            }
        } else {
            return false;
        }
        $hd=opendir($dest);
        while ($read=readdir($hd)) {
            if (strcmp($read, '.') !== 0 && strcmp($read, '..') !==0) {
                if (self::isDir($dest.'/'.$read)) {
                    $index = $dest.'/'.$read.'/'.conf('defaultIndex', 'index.html');
                    if (!self::exist($index)) {
                        file_put_contents($index, $content);
                    }
                    self::touchIndex($dest.'/'.$read, $content);
                }
            }
        }
        closedir($hd);
        return true;
    }
}
