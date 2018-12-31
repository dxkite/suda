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
use suda\core\storage\iterator\PathPregFilterIterator;

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
    public function mkdirs(string $dir, int $mode=0755):bool
    {
        $path=Autoloader::parsePath($dir);
        if (!$this->isDir($path)) {
            if (!$this->mkdirs(dirname($path), $mode)) {
                return false;
            }
            if (!$this->mkdir($path, $mode)) {
                return false;
            }
        }
        return true;
    }
    
    public function path(string $path):?string
    {
        $path=Autoloader::parsePath($path);
        return $this->exist($path)?$path:$this->mkdirs($path)?$path:null;
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
        foreach ($this->readPath($parent, $repeat, $preg, $full) as $file) {
            $path = $full?$file:$parent.DIRECTORY_SEPARATOR.$file;
            if ($this->isFile($path)) {
                yield $file;
            }
        }
    }

    public function readDirs(string $parent, bool $repeat=false, ?string $preg=null, bool $full=false): \Iterator
    {
        $parent=Autoloader::parsePath($parent);
        foreach ($this->readPath($parent, $repeat, $preg, $full) as $dir) {
            $path = $full?$dir:$parent.DIRECTORY_SEPARATOR.$dir;
            if ($this->isDir($path)) {
                yield $dir;
            }
        }
    }

    public function readPath(string $parent, bool $repeat=false, ?string $preg=null, bool $full=true): \Iterator
    {
        $directory=Autoloader::parsePath($parent);
        if ($this->isDir($directory)) {
            if ($repeat) {
                $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
            } else {
                $it = new \RecursiveDirectoryIterator($directory);
            }
            $it = new PathPregFilterIterator($it, $preg);
            foreach ($it as $key => $item) {
                if ($full) {
                    yield $key;
                } else {
                    yield $this->cut($key, $directory);
                }
            }
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
        if ($this->isFile($path)) {
            $this->remove($path);
        } elseif ($this->isDir($path)) {
            $this->rmdirs($path);
        }
        return $this->exist($path) === false;
    }

    /**
     * 递归删除文件夹
     *
     * @param string $parent
     * @return boolean
     */
    public function rmdirs(string $parent):bool
    {
        if ($this->isDir($parent)) {
            foreach ($this->readPath($parent) as $path) {
                if ($this->isFile($path)) {
                    $errorhandler = function ($erron, $error, $file, $line) {
                        Debug::warning($error);
                    };
                    set_error_handler($errorhandler);
                    unlink($path);
                    restore_error_handler();
                }
                if ($this->isEmpty($path)) {
                    rmdir($path);
                } else {
                    $this->rmdirs($path);
                }
            }
            rmdir($parent);
            return true;
        }
        return false;
    }

    public function isEmpty(string $dirOpen):bool
    {
        while ($this->readDirs($dirOpen)) {
            return false;
        }
        return true;
    }

    public function copydir(string $src, string $dest, ?string $preg=null):bool
    {
        if ($path = $this->path($dest)) {
            foreach ($this->readPath($src, false, $preg, false) as $read) {
                if ($this->isDir($src.DIRECTORY_SEPARATOR.$read)) {
                    $this->copydir($src.DIRECTORY_SEPARATOR.$read, $dest.DIRECTORY_SEPARATOR.$read, $preg);
                } else {
                    $this->copy($src.DIRECTORY_SEPARATOR.$read, $dest.DIRECTORY_SEPARATOR.$read);
                }
            }
            return true;
        } else {
            return false;
        }
    }
    
    public function movedir(string $src, string $dest, ?string $preg=null):bool
    {
        if ($path = $this->path($dest)) {
            foreach ($this->readPath($src, false, $preg, false) as $read) {
                if ($this->isDir($src.DIRECTORY_SEPARATOR.$read)) {
                    $this->movedir($src.DIRECTORY_SEPARATOR.$read, $dest.DIRECTORY_SEPARATOR.$read, $preg);
                    $this->rmdir($src.DIRECTORY_SEPARATOR.$read);
                } else {
                    $this->move($src.DIRECTORY_SEPARATOR.$read, $dest.DIRECTORY_SEPARATOR.$read);
                }
            }
            $this->rmdir($src);
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
        if ($this->exist($src)) {
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
        if ($this->exist($src)) {
            return rename($src, $dest);
        }
        return false;
    }

    // 创建文件夹
    public function mkdir(string $path, int $mode=0755):bool
    {
        $path=Autoloader::parsePath($path);
        if (!$this->isDir($path) && is_writable(dirname($path))) {
            $mk = mkdir($path, $mode);
            if ($mk) {
                chmod($path, $mode);
            }
            return $mk;
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
        if ($this->isDir($dirname) && is_writable($dirname)) {
            return file_put_contents($name, $content, $flags);
        }
        return false;
    }

    public function get(string $name):string
    {
        $name=Autoloader::parsePath($name);
        if ($file=$this->exist($name)) {
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
        if ($file=$this->exist($name)) {
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
        if ($file=$this->exist($name)) {
            if (is_string($file)) {
                $name=$file;
            }
            return filesize($name);
        }
        return 0;
    }

    public function download(string $url, string $save):bool
    {
        $save=Autoloader::parsePath($save);
        return $this->put($save, $this->curl($url));
    }
    
    public function curl(string $url, int $timeout=3)
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
        if ($file=$this->exist($name)) {
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
        if ($this->existCase($path)) {
            return true;
        }
        // Windows 文件中文编码
        $charset=array_merge(self::$charset, $charset);
        foreach ($charset as $code) {
            $file = iconv('UTF-8', $code, $path);
            if ($file && $this->existCase($file)) {
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
}
