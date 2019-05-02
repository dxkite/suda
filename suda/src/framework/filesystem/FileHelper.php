<?php
namespace suda\framework\filesystem;

use suda\framework\loader\Path;

/**
 * 文件辅助函数
 */
trait FileHelper
{
    /**
     * 判断文件是否存在
     *
     * @param string $path
     * @return boolean
     */
    public static function exist(string $path):bool
    {
        return Path::format($path) !== null;
    }

    /**
     * 删除文件
     *
     * @param string $filename
     * @return boolean
     */
    public static function delete(string $filename):bool
    {
        if (($path=Path::format($filename)) !== null) {
            if (!is_writable($path)) {
                return false;
            }
            return unlink($path);
        }
        return true;
    }

    /**
     * 移动文件
     *
     * @param string $src
     * @param string $dest
     * @return boolean
     */
    public static function move(string $src, string $dest):bool
    {
        if (($path=Path::format($src)) !== null && is_writable(dirname($dest))) {
            return rename($path, $dest);
        }
        return false;
    }

    /**
     * 复制文件
     *
     * @param string $src
     * @param string $dest
     * @return boolean
     */
    public static function copy(string $src, string $dest):bool
    {
        if (($path=Path::format($src)) !== null && is_writable(dirname($dest))) {
            return copy($path, $dest);
        }
        return false;
    }

    /**
     * 写入文件
     *
     * @param string $filename
     * @param string $content
     * @param integer $flags
     * @return boolean
     */
    public static function put(string $filename, string $content, int $flags = 0):bool
    {
        if (is_writeable(dirname($filename))) {
            return file_put_contents($filename, $content, $flags) > 0;
        }
        return false;
    }

    /**
     * 读取文件
     *
     * @param string $filename
     * @return string|null
     */
    public static function get(string $filename):?string
    {
        if (is_readable($filename) && ($path=Path::format($filename)) !== null) {
            return file_get_contents($path);
        }
        return null;
    }
}
