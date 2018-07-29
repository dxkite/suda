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
 * TODO 切换全部配置文件支持yml配置
 */
class Config
{
    public static $config=[];

    public static function load(string $path)
    {
        $data = self::loadConfig($path);
        if ($data) {
            return self::assign($data);
        }
    }

    public static function loadConfig(string $path):?array
    {
        $data=null;
        if (file_exists($path)) {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            switch ($ext) {
                case 'yml':
                    if (function_exists('yaml_parse_file')) {
                        $data = yaml_parse_file($path);
                    } else {
                        $message =__('parse yaml config error %s: missing yaml extension', $path);
                        debug()->error($message);
                        suda_panic('Kernal Panic', $message);
                    }
                    break;
                case 'php':
                    $data = include $path;
                    break;
                case 'json':
                default:
                    $data = json_decode(file_get_contents($path), true);
            }
        } elseif (function_exists('yaml_parse_file') && file_exists($config = $path.'.yml')) {
            $data = yaml_parse_file($config);
        } elseif (file_exists($config = $path.'.json')) {
            $data = json_decode(file_get_contents($config), true);
        } elseif (file_exists($config = $path.'.php')) {
            $data = include $config;
        }
        return $data;
    }

    public static function exist(string $path)
    {
        if (file_exists($path)) {
            return $path;
        } else {
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            if (empty($ext)) {
                $basepath = $path;
            } else {
                $basepath = preg_replace('/\.'.$ext.'$/','',$path);
            }
            if (file_exists($conf = $basepath.'.yml') && function_exists('yaml_parse_file')) {
                return $conf;
            } elseif (file_exists($conf =$basepath.'.json')) {
                return $conf;
            } elseif (file_exists($conf =$basepath.'.php')) {
                return $conf;
            }
        }
        return false;
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
