<?php

namespace suda\database;

use JsonSerializable;
use PDO;

/**
 * 数据输入值
 * 用于处理模板输入值
 */
class Binder implements JsonSerializable
{
    /**
     * @var string
     */
    private $key;
    /**
     * @var string
     */
    private $name;
    /**
     * @var mixed
     */
    private $value;

    /**
     * @var int
     */
    protected static $index = 0;

    /**
     * Binder constructor.
     * @param string $name 绑定名
     * @param mixed $value 绑定值
     * @param string|null $key SQL键名
     */
    public function __construct(string $name, $value, string $key = null)
    {
        $this->name = $name;
        $this->value = $value;
        $this->key = $key;
        static::$index++;
    }

    /**
     * @param string $name
     * @return string
     */
    public static function index(string $name): string
    {
        return '_' . $name . '_' . static::$index;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }


    /**
     * @return mixed
     */
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

    /**
     * @return string|null
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'key' => $this->key,
            'value' => $this->value,
        ];
    }

    /**
     * @return false|string
     */
    public function __toString()
    {
        return json_encode($this->jsonSerialize(), JSON_UNESCAPED_UNICODE);
    }
}
