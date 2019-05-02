<?php
namespace suda\orm;

use JsonSerializable;
use PDO;

/**
 * 数据输入值
 * 用于处理模板输入值
 */
class Binder implements JsonSerializable
{
    private $key;
    private $name;
    private $value;

    protected static $index = 0;

    public function __construct(string $name, $value, string $key = null)
    {
        $this->name = $name;
        $this->value = $value;
        $this->key = $key;
        static::$index ++;
    }

    public static function index(string $name):string
    {
        return '_'.static::$index.$name;
    }

    public function getName():string
    {
        return $this->name;
    }


    public function getValue()
    {
        return $this->value;
    }

    /**
     * 创建值的类型
     *
     * @param mixed $value
     * @return int
     */
    public static function typeOf($value)
    {
        if (null === $value) {
            $type = PDO::PARAM_NULL;
        } elseif (is_bool($value)) {
            $type = PDO::PARAM_BOOL;
        } elseif (is_numeric($value) && intval($value) === $value) {
            $type = PDO::PARAM_INT;
        } else {
            $type = PDO::PARAM_STR;
        }
        return $type;
    }

    public function getKey()
    {
        return $this->key;
    }
    
    public function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'key' => $this->key,
            'value' => $this->value,
        ];
    }

    public function __toString()
    {
        return json_encode($this->jsonSerialize(), JSON_UNESCAPED_UNICODE);
    }
}
