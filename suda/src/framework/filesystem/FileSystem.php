<?php
namespace suda\framework\filesystem;

use suda\framework\loader\Path;
use suda\framework\loader\PathTrait;
use suda\framework\filesystem\FileHelper;
use suda\framework\filesystem\DirectoryHelper;

/**
 * 文件辅助函数
 */
class FileSystem implements FileSystemInterface
{
    use DirectoryHelper {
        rm as protected;
        rmdirs as protected;
        move as moveDir;
        copy as copyDir;
    }

    use FileHelper  {
       delete as protected deleteFile;
       FileHelper::copy insteadof DirectoryHelper;
       FileHelper::move insteadof DirectoryHelper;
    }

    /**
     * 判断是否溢出路径
     *
     * @param string $root
     * @param string $target
     * @return boolean
     */
    public static function isOverflowPath(string $root, string $target)
    {
        $abslute = PathTrait::toAbsolutePath($target);
        $root = PathTrait::toAbsolutePath($root);
        return strpos($abslute, $root) !== 0;
    }

    /**
     * 删除文件或者目录
     *
     * @param string $path
     * @return boolean
     */
    public static function delete(string $path):bool
    {
        if (($path = Path::format($path)) !== null) {
            if (is_file($path)) {
                return static::deleteFile($path);
            }

            if (is_dir($path)) {
                return static::rmDirs($path);
            }
        }
        return false;
    }

    /**
     * 是否可写
     *
     * @param string $path
     * @return boolean
     */
    public static function isWritable(string $path):bool
    {
        $writable = false;
        \set_error_handler(null);
        if (DIRECTORY_SEPARATOR === '/' && ini_get('safe_mode') == false) {
            $writable = is_writable($path);
        }
        if (is_dir($path)) {
            $path = rtrim($path, '/').'/'.md5(mt_rand(1, 100).mt_rand(1, 100));
            if (($fp = fopen($path, 'ab')) === false) {
                $writable = false;
            } else {
                fclose($fp);
                chmod($path, 0777);
                unlink($path);
                $writable = true;
            }
        } elseif (($fp = fopen($path, 'ab')) === false) {
            $writable = false;
        }
        \restore_error_handler();
        return $writable;
    }
}
