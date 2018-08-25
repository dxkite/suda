<?php
/**
 * Suda FrameWork
 *
 * An open source application development framework for PHP 7.0.0 or newer
 *
 * Copyright (c)  2017 DXkite
 *
 * @category   PHP FrameWork
 * @package    Suda
 * @copyright  Copyright (c) DXkite
 * @license    MIT
 * @link       https://github.com/DXkite/suda
 * @version    since 1.2.4
 */
namespace suda\core;

use suda\tool\Json;
use suda\tool\ArrayHelper;

/**
 * 文件配置类
 */
class Config
{
    public static $config=[];

    public static function load(string $path, ?string $module=null)
    {
        $data = self::loadConfig($path, $module);
        if ($data) {
            return self::assign($data);
        }
    }

    public static function loadConfig(string $path, ?string $module=null):?array
    {
        $data=null;
        if (!file_exists($path)) {
            $path = self::resolve($path);
        }
        if ($path) {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            switch ($ext) {
                case 'yml':
                    if (function_exists('yaml_parse')) {
                        $content = file_get_contents($path);
                        $content = self::parseValue($content, $module);
                        $data = yaml_parse($content);
                    } elseif (class_exists('Spyc')) {
                        $content = file_get_contents($path);
                        $content = self::parseValue($content, $module);
                        $data =\Spyc::YAMLLoadString($content);
                    } else {
                        $message =__('parse yaml config error %s: missing yaml extension or spyc', $path);
                        debug()->error($message);
                        suda_panic('Kernal Panic', $message);
                    }
                    break;
                case 'php':
                    $data = include $path;
                    break;
                case 'json':
                default:
                    $content = file_get_contents($path);
                    $content = self::parseValue($content, $module);
                    $data = json_decode($content, true);
            }
        }
        return $data;
    }

    protected static function parseValue(string $content,?string $module):string
    {
        return preg_replace_callback('/\$\{(\w+)\}/', function ($matchs) use ($module) {
            $name = $matchs[1];
            if ($name === 'module' && $module) {
                return $module;
            }
            if ($value = constant($name)) {
                return $value;
            }
            if ($value = conf($name,null)) {
                return $value;
            }
            return $name;
        }, $content);
    }

    public static function resolve(string $path):?string
    {
        if (file_exists($path)) {
            return $path;
        } else {
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            if (empty($ext)) {
                $basepath = $path;
            } else {
                $basepath = preg_replace('/\.'.$ext.'$/', '', $path);
            }
            if (file_exists($conf = $basepath.'.yml') && function_exists('yaml_parse')) {
                return $conf;
            } elseif (class_exists('Spyc') && file_exists($conf = $basepath.'.yml')) {
                return $conf;
            } elseif (file_exists($conf=$basepath.'.json')) {
                return $conf;
            } elseif (file_exists($conf=$basepath.'.php')) {
                return $conf;
            }
        }
        return null;
    }

    public static function exist(string $path):bool
    {
        return self::resolve($path) != null;
    }

    public static function assign(array $config)
    {
        return self::$config=array_merge(self::$config, $config);
    }

    public static function get(string $name=null, $default=null)
    {
        if (is_null($name)) {
            return self::$config;
        }
        return ArrayHelper::get(self::$config, $name, $default);
    }

    public static function set(string $name, $value, $combine=null)
    {
        return ArrayHelper::set(self::$config, $name, $value, $combine);
    }

    public static function has(string $name)
    {
        return ArrayHelper::exist(self::$config, $name);
    }
}
