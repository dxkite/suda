<?php
namespace suda\framework\loader;

/**
 * 自动加载路径处理
 *
 */
interface PathInterface
{
    /**
     * @param string $path
     * @return string
     */
    public static function formatSeparator(string $path):string;

    /**
     * @param string $path
     * @param string $separator
     * @return string
     */
    public static function toAbsolutePath(string $path, string $separator = DIRECTORY_SEPARATOR):string;

    /**
     * @return string|null
     */
    public static function getHomePath():?string;
}
