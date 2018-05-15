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
namespace suda\tool;

/**
 * 数组操纵
 * 
 * 设置值， 获取值，导出成文件
 */
class ArrayHelper
{
    /**
    * 获取数组元素
    *
    * @example
    * array_get_value('a.b.c.d',$arr);
    * 返回 $arr['a']['b']['c']['d'];
    *  
    * @param  $name 查询列
    * @param  $array 查询的数组
    * @return mixed 查询的值
    */
    public static function get(array $array, string $name, $def = null)
    {
        $path = explode('.', $name);
        while ($key = array_shift($path)) {
            if (array_key_exists($key, $array)) {
                $array = $array[$key];
            } else {
                return $def;
            }
        }
        return $array;
    }

    public static function exist(array $array, string $name)
    {
        $path = explode('.', $name);
        while ($key = array_shift($path)) {
            if (array_key_exists($key, $array)) {
                $array = $array[$key];
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * 设置数组的值
     *
     * @param array $array
     * @param string $name
     * @param [type] $value
     * @param [type] $def
     * @return void
     */
    public static function set(array &$array, string $name, $value, $def=null)
    {
        $path = explode('.', $name);
        $ptr = &$array;
        while (count($path) > 1) {
            $key = array_shift($path);
            if (!array_key_exists($key, $array)) {
                $array[$key] = [];
            }
            $array = &$array[$key];
        }
        $key = $path[0];
        if (array_key_exists($key, $array) && is_array($array[$key]) && is_array($value)) {
            $array[$key] = array_merge($array[$key], is_array($def)?$def:[], $value);
        } else {
            $array[$key] = is_null($value) ? $def : $value;
        }
        return $ptr;
    }

    /**
     * 将数组导出
     *
     * @param $path 路径
     * @param $name 导出的数组名
     * @param $array 导出的数组
     */
    public static function export(string $path, string $name, array $array, bool $sort = true, bool $beautify=false)
    {
        if ($beautify) {
            $name = '$'.ltrim($name, '$');
            $exstr = "<?php\r\n".$name."=array();\r\n";
            //@notice# 排序数组时可能会导致数据丢失
            if ($sort) {
                ksort($array);
            }
            $exstr .= self::arr2string($name, $array);
            $exstr .= 'return '.$name.';';
        } else {
            $exstr = "<?php\r\nreturn ". var_export($array, true).";\r\n";
        }
        return file_put_contents($path, $exstr) ? true : false;
    }


    // 数目不同
    protected static function combine(array $key, array $value)
    {
        if (count($name)!==count($preg)) {
            $value=array_slice($value, 0, count($name));
        }
        return array_combine($key, $value);
    }

    protected static function arr2string($arrname, $array)
    {
        $exstr = '';
        foreach ($array as $key => $value) {
            $line = '';
            $current=$arrname."['".addslashes($key)."']";
            if (is_array($value)) {
                $line .= self::parserArraySub($current, $value);
            } else {
                $line =  $current;
                if (is_string($value)) {
                    $line .= "='".addslashes($value)."';\r\n";
                } elseif (is_bool($value)) {
                    $line .= '='.($value ? 'true' : 'false').";\r\n";
                } elseif (is_null($value)) {
                    $line .= "=null;\r\n";
                } else {
                    $line .= "=$value;\r\n";
                }
            }
            $exstr .= $line;
        }
        return $exstr;
    }

    protected static function parserArraySub(string $parent, array $array)
    {
        $line = '';
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $subpar = $parent."['".$key."']";
                $line .= self::parserArraySub($subpar, $value);
            } else {
                $line .= $parent."['".$key."']";
                if (is_string($value)) {
                    $line .= "='".addslashes($value)."';\r\n";
                } elseif (is_bool($value)) {
                    $line .= '='.($value ? 'true' : 'false').";\r\n";
                } elseif (is_null($value)) {
                    $line .= "=null;\r\n";
                } else {
                    $line .= "=$value;\r\n";
                }
            }
        }
        return $line;
    }
}
