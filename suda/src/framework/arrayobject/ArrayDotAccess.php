<?php
namespace suda\framework\arrayobject;

use function array_key_exists;
use ArrayAccess;
use function is_array;

/**
 * 数组点获取类
 */
class ArrayDotAccess implements ArrayAccess
{
    protected $value;

    /**
     * 创建对象
     *
     * @param array|ArrayAccess $array
     */
    public function __construct($array)
    {
        $this->value = $array;
    }

    public function offsetSet($offset, $value)
    {
        if (null === $offset) {
            $this->value[] = $value;
        } else {
            static::set($this->value, $offset, $value);
        }
    }

    public function offsetExists($offset)
    {
        return static::exist($this->value, $offset);
    }

    public function offsetUnset($offset)
    {
        static::unset($this->value, $offset);
    }

    public function offsetGet($offset)
    {
        return static::get($this->value, $offset);
    }

    /**
     * 获取数组元素
     *
     * @param array|ArrayAccess $array
     * @param string $name 查询列
     * @param mixed $defaultValue 查询的默认值
     * @return mixed 查询的值
     */
    public static function get($array, string $name, $defaultValue = null)
    {
        $path = explode('.', $name);
        while ($key = array_shift($path)) {
            if (static::keyExist($key, $array)) {
                $array = $array[$key];
            } else {
                return $defaultValue;
            }
        }
        return $array ?? $defaultValue;
    }

    /**
     * 检查元素是否存在
     *
     * @param array|ArrayAccess $array
     * @param string $name
     * @return boolean
     */
    public static function exist($array, string $name)
    {
        $path = explode('.', $name);
        while ($key = array_shift($path)) {
            if (static::keyExist($key, $array)) {
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
     * @param array|ArrayAccess $array
     * @param string $name
     * @param mixed $value
     * @return array|ArrayAccess 设置后的数组
     */
    public static function set(&$array, string $name, $value)
    {
        $path = explode('.', $name);
        $root = &$array;
        while (count($path) > 1) {
            $key = array_shift($path);
            if (static::keyExist($key, $array) === false) {
                $array[$key] = [];
            }
            $array = &$array[$key];
        }
        $key = array_shift($path);
        $array[$key] = $value;
        return $root;
    }

    /**
     * 删除值
     *
     * @param array|ArrayAccess $array
     * @param string $name
     * @return void
     */
    public static function unset(&$array, string $name)
    {
        $path = explode('.', $name);
        while (count($path) > 1) {
            $key = array_shift($path);
            if (static::keyExist($key, $array) === false) {
                $array[$key] = [];
            }
            $array = &$array[$key];
        }
        $key = array_shift($path);
        unset($array[$key]);
    }

    /**
     * 判断数组对象是否存在
     *
     * @param string $key
     * @param mixed $value
     * @return boolean
     */
    public static function keyExist(string $key, $value):bool
    {
        if (is_array($value) && array_key_exists($key, $value)) {
            return true;
        }
        if ($value instanceof ArrayAccess) {
            return $value->offsetExists($key);
        }
        return false;
    }

    /**
     * 判断是否为可数组访问对象
     *
     * @param mixed $value
     * @return boolean
     */
    public static function isArray($value)
    {
        return is_array($value) || $value instanceof ArrayAccess;
    }
}
