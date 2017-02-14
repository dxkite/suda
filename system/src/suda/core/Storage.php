<?php
namespace suda\core;

class Storage
{
    public static $charset=['GBK','GB2312','BIG5'];
    // 递归创建文件夹
    public static function mkdirs(string $dir, int $mode=0777):bool
    {
        if (!self::isDir($dir)) {
            if (!self::mkdirs(dirname($dir), $mode)) {
                return false;
            }
            if (!@mkdir($dir, $mode)) {
                return false;
            }
        }
        return true;
    }
    
    public static function path(string $path){
        self::mkdirs($path);
        return realpath($path);
    }

    public static function readDirFiles(string $dirs,  bool $repeat=false, string $preg='/^.+$/',bool $cut=false):array
    {
        $file_totu=[];
        $dirs=realpath($dirs);
        if (self::isDir($dirs)) {
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
        }
        if ($cut){
            $cutfile=[];
            foreach ($file_totu as $file){
                $cutfile[]=self::cutPath($file,$dirs);
            }
            return $cutfile;
        }
        return $file_totu;
    }
    public static function cutPath(string $path,string $basepath=ROOT_PATH){
        return trim(preg_replace('/^'.preg_quote($basepath,'/').'/','',$path),'\\/');
    }
    public static function readDirs(string $dirs, bool $repeat=false, string $preg='/^.+$/'):array
    {
        $reads=[];
        if (self::isDir($dirs)) {
            $hd=opendir($dirs);
            while ($read=readdir($hd)) {
                if (strcmp($read ,'.') !== 0 && strcmp($read, '..') !==0) {
                    $path=$dirs.'/'.$read;
                    if (self::isDir($path) && preg_match($preg, $read)) {
                        $reads[]=$read;
                        if ($repeat) {
                            foreach (self::readDirs($path) as $read) {
                                $reads[]=$read;
                            }
                        }
                    }
                }
            }
        }
        return $reads;
    }

    // 递归删除文件夹
    public static function rmdirs(string $dir)
    {

        if (self::isDir($dir) && $handle=opendir($dir)) {
            while (false!== ($item=readdir($handle))) {
                if ($item!="."&&$item!="..") {
                    if (self::isDir("{$dir}/{$item}")) {
                        self::rmdirs("{$dir}/{$item}");
                    } elseif (file_exists("{$dir}/{$item}")) { // Non-Thread-Safe
                        // Need thread-safe version to avoid this error
                        $errorhandler=function($erron, $error, $file, $line){
                            Debug::w($error);
                        };
                        set_error_handler($errorhandler);
                        unlink("{$dir}/{$item}");
                        restore_error_handler();
                    }
                }
            }
        }
    }
    
    public static function copy(string $source, string $dest):bool
    {
        if (self::exist($source)) {
            return copy($source, $dest);
        }
        return false;
    }
    public static function move(string $src, string $dest):bool
    {
        if (self::exist($src)) {
            return rename($src, $dest);
        }
        return false;
    }
    // 创建文件夹
    public static function mkdir(string $dirname, int $mode=0777):bool
    {
        return mkdir($path, $mode);
    }
    // 删除文件夹
    public static function rmdir(string $dirname):bool
    {
        return rmdir($path);
    }
    public static function put(string $name, $content, int $flags = 0):bool
    {
        if (self::isDir(dirname($name))) {
            return file_put_contents($name, $content, $flags);
        }
        return false;
    }

    public static function get(string $name):string
    {
        if ($file=self::exist($name)) {
            if (is_string($file)) {
                $name=$file;
            }
            return file_get_contents($name);
        }
        return '';
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function remove(string $name) : bool
    {
        if ($file=self::exist($name)) {
            if (is_string($file)) {
                $name=$file;
            }
            return unlink($name);
        }
        return true;
    }
    
    public static function isFile(string $name):bool
    {
        return is_file($name);
    }

    public static function isDir(string $name):bool
    {
        return is_dir($name);
    }

    public static function isReadable(string $name):bool
    {
        return is_readable($name);
    }
    public static function isWritable(string $name):bool
    {
        return is_writable($name);
    }
    
    public static function size(string $name):int
    {
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
        $file=file_get_contents($url);
        return file_put_contents($save, $file);
    }
    public static function type(string $name):int
    {
        if ($file=self::exist($name)) {
            if (is_string($file)) {
                $name=$file;
            }
            return filetype($name);
        }
        return 0;
    }

    public static function exist(string $name, array $charset=[])
    {
        // UTF-8 格式文件路径
        if (self::exist_case($name)) {
            return true;
        }
        // Windows 文件中文编码
        $charset=array_merge(self::$charset, $charset);
        foreach ($charset as $code) {
            $file = iconv('UTF-8', $code, $name);
            if (self::exist_case($file)) {
                return $file;
            }
        }
        return false;
    }

    // 判断文件存在
    private static function exist_case($name):bool
    {
        if (file_exists($name) && is_file($name) && $real=realpath($name)) {
            if (basename($real) === basename($name)) {
                return true;
            }
        }
        return false;
    }
}
