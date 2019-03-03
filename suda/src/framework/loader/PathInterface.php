<?php
namespace suda\framework\loader;

/**
 * 自动加载路径处理
 *
 */
interface PathInterface
{
    public static function formatSeparator(string $path):string;

    public static function toAbsolutePath(string $path, string $separator = DIRECTORY_SEPARATOR):string;

    public static function getHomePath():?string;
}
