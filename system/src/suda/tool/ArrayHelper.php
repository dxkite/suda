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
 * 数组操纵，
 * 设置值，
 * 获取值
 * 导出成文件
*/
class ArrayHelper
{
    /**
    * @ref  获取数组元素
    * @param  $name 查询列
    * <code>
    * array_get_value('a.b.c.d',$arr);
    * 返回 $arr['a']['b']['c']['d'];
    * </code>
    * @param  $array 查询的数组
    * @return mixed 查询的值
    */
    public static function get(array $array, string $name, $def = null)
    {
        $path = explode('.', $name);
        // 二级数组
        if (count($path) > 1) {
            // 取头
            $next = array_shift($path);
            // 取值的键
            $aim = array_pop($path);
            // 数组偏移到下一级
            $array =   $array[$next] ?? null;

            if (count($path) > 1) {
                # >4级

                while (($next = array_shift($path)) || $array !== null) {
                    if (is_null($next)) {
                        break;
                    }

                    $array =   $array[$next] ??  null;
                }

                if ($array === null) {
                    return $def;
                }

                $value =  $array[$aim] ?? $def;
            } elseif (count($path) === 1) {
                #3级

                if (isset($array[$path[0]]) && is_array($array[$path[0]])) {
                    $value =   $array[$path[0]][$aim] ?? $def;
                } else {
                    $value = $def;
                }
            } else {
                #2级

                if (is_array($array)) {
                    $value =  $array[$aim] ?? $def;
                } else {
                    $value = $def;
                }
            }
        } else {
            $value =  $array[$name] ?? $def;
        }
        return $value;
    }

    public static function set(array &$array, string $name, $value, $def=null)
    {
        if (strpos($name, '.')) {
            $pos = explode('.', $name);
            $offset = &$array;
            $next = null;
            while ($next = array_shift($pos)) {
                if (isset($offset[$next])) {
                    $offset = &$offset[$next];
                } else {
                    $offset[$next] = array();
                    $offset = &$offset[$next];
                }
            }
            // 如果 设置的是数组，则合并
            if ($offset && is_array($offset) && is_array($value)) {
                $offset = array_merge($offset, is_array($def)?$def:[], $value);
            } else {
                $offset = is_null($value) ? $def : $value;
            }
        } else {
            if (isset($array[$name]) && is_array($array[$name]) && is_array($value)) {
                $array[$name]=array_merge($array[$name], is_array($def)?$def:[], $value);
            } else {
                $array[$name] = $value;
            }
        }
        return $array;
    }

    /**
    * @ref 将数组导出
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
