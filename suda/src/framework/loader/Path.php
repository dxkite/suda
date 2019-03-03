<?php
namespace suda\framework\loader;

/**
 * 文件路径推测
 * 包括非UTF-8路径以及大小写敏感
 */
class Path
{
    /**
     * 格式化路径
     *
     * @param string $path 目标路径
     * @return string|null 返回格式化结果，如果路径不存在则返回NULL
     */
    public static function format(string $path):?string
    {
        return static::existCharset($path, ['GBK','GB2312','BIG5']);
    }
 
    public static function existCharset(string $path, array $charset):?string
    {
        $abspath = static::toAbsolutePath($path);
        if (static::existCase($abspath)) {
            return $abspath;
        }
        foreach ($charset as $code) {
            $pathCode = iconv('UTF-8', $code, $abspath);
            if ($pathCode !== false && static::existCase($pathCode)) {
                return $pathCode;
            }
        }
        return null;
    }

    /**
     * 区分大小写的路径文件存在性判断
     *
     * @param string $path
     * @return boolean
     */
    public static function existCase(string $path):bool
    {
        $abspath = static::toAbsolutePath($path);
        // 真实文件系统
        if (realpath($abspath) === $abspath) {
            return true;
        }
        // 虚拟文件系统
        if (\file_exists($abspath)) {
            foreach (stream_get_wrappers() as $wrapper) {
                if (strpos($abspath, $wrapper.'://') === 0) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function toAbsolutePath(string $path)
    {
        return PathTrait::toAbsolutePath($path);
    }

    /**
     * 判断是否为相对路径
     *
     * @param string $path
     * @return boolean
     */
    public static function isRelativePath(string $path):bool
    {
        $path = \str_replace('\\', '/', $path);
        return !(strpos($path, ':/') > 0 || strpos($path, '/') === 0);
    }
}
