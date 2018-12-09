<?php
/**
 * Suda FrameWork
 *
 * An open source application development framework for PHP 7.2.0 or newer
 * 
 * Copyright (c)  2017-2018 DXkite
 *
 * @category   PHP FrameWork
 * @package    Suda
 * @copyright  Copyright (c) DXkite
 * @license    MIT
 * @link       https://github.com/DXkite/suda
 * @version    since 1.2.13
 */

namespace suda\archive\creator;

use PDO;

/**
 * 数据输入值
 * 用于处理模板输入值
 */
class InputValue implements \JsonSerializable
{
    private $name;
    private $value;


    public function __construct(string $name, $value, int $bindType=PDO::PARAM_STR)
    {
        $this->name=$name;
        $this->value=$value;
    }

    public function getName():string
    {
        return $this->name;
    }


    public function getValue()
    {
        return $this->value;
    }

    public static function bindParam($value)
    {
        if (is_null($value)) {
            $type=PDO::PARAM_NULL;
        } elseif (is_bool($value)) {
            $type=PDO::PARAM_BOOL;
        } elseif (is_numeric($value) && intval($value) === $value) {
            $type=PDO::PARAM_INT;
        } else {
            $type=PDO::PARAM_STR;
        }
        return $type;
    }

    
    public function jsonSerialize()
    {
        return [
            'name'=>$this->name,
            'value'=>$this->value
        ];
    }

    public function __toString()
    {
        return json_encode($this->jsonSerialize(), JSON_UNESCAPED_UNICODE);
    }
}
