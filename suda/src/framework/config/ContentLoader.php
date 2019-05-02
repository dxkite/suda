<?php
namespace suda\framework\config;

use function call_user_func_array;
use function parse_ini_string;
use suda\framework\exception\JsonException;
use suda\framework\exception\YamlException;
use suda\framework\arrayobject\ArrayDotAccess;

/**
 * 配置文件加载器
 * 支持 yaml,yml,json,php,ini 做配置文件
 */
class ContentLoader
{
    public static function loadJson(string $path, array $extra = []):array
    {
        $content = file_get_contents($path);
        $content = static::parseValue($content, $extra);
        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new JsonException('load json config error : '.json_last_error());
        }
        return $data;
    }

    public static function loadPhp(string $path, array $extra = []):array
    {
        $data = include $path;
        return $data ?? [];
    }

    public static function loadIni(string $path, array $extra = []):array
    {
        $content = file_get_contents($path);
        $content = static::parseValue($content, $extra);
        return parse_ini_string($content, true) ?: [];
    }
    
    public static function loadYml(string $path, array $extra = []):array
    {
        return static::loadYaml($path, $extra);
    }

    public static function loadYaml(string $path, array $extra = []):array
    {
        if (function_exists('yaml_parse')) {
            $name = 'yaml_parse';
        } elseif (class_exists('Spyc')) {
            $name = 'Spyc::YAMLLoadString';
        } else {
            throw new YamlException('load yaml config error : missing yaml extension or spyc', 1);
        }
        $content = file_get_contents($path);
        $content = static::parseValue($content, $extra);
        return call_user_func_array($name, [$content]);
    }

    protected static function parseValue(string $content, array $extra = []):string
    {
        return preg_replace_callback('/\$\{(.+?)\}/', function ($matchs) use ($extra) {
            $name = $matchs[1];
            if (($value = ArrayDotAccess::get($extra, $name, null)) !== null) {
            } elseif (defined($name)) {
                $value = constant($name);
            }
            return is_string($value)?trim(json_encode($value), '"'):$value;
        }, $content);
    }
}
