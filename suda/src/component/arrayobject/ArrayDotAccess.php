<?php
namespace suda\component\arrayobject;

/**
 * 数组点获取类
 */
class ArrayDotAccess implements \ArrayAccess
{
    protected $value;

    public function __construct(array $array)
    {
        $this->value = $array;
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
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
     * @param array $array
     * @param string $name 查询列
     * @param mixed $def 查询的默认值
     * @return mixed 查询的值
     */
    public static function get(array $array, string $name, $def = null)
    {
        $path = explode('.', $name);
        while ($key = array_shift($path)) {
            if (is_array($array) && array_key_exists($key, $array)) {
                $array = $array[$key];
            } else {
                return $def;
            }
        }
        return $array;
    }

    /**
     * 检查元素是否存在
     *
     * @param array $array
     * @param string $name
     * @return boolean
     */
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
     * @param mixed $value
     * @param mixed $def
     * @return array 设置后的数组
     */
    public static function set(array &$array, string $name, $value, $def=null):array
    {
        $path = explode('.', $name);
        $root = &$array;
        while (count($path) > 1) {
            $key = array_shift($path);
            if (is_array($array)) {
                if (!array_key_exists($key, $array)) {
                    $array[$key] = [];
                }
            } else {
                $array=[];
            }
            $array = &$array[$key];
        }
        $key = array_shift($path);
        if (is_array($array) && array_key_exists($key, $array) && is_array($array[$key]) && is_array($value)) {
            $array[$key] = array_merge($array[$key], is_array($def) ? $def : [], $value);
        } else {
            $array[$key] = is_null($value) ? $def : $value;
        }
        return $root;
    }

    public static function unset(array &$array, string $name)
    {
        $path = explode('.', $name);
        while (count($path) > 1) {
            $key = array_shift($path);
            if (is_array($array)) {
                if (!array_key_exists($key, $array)) {
                    $array[$key] = [];
                }
            } else {
                $array=[];
            }
            $array = &$array[$key];
        }
        $key = array_shift($path);
        unset($array[$key]);
    }

    /**
     * 转换成正常数组
     *
     * @return array
     */
    public function toArray():array {
        return $this->value;
    }
}
