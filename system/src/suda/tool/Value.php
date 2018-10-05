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
 * @version    since 1.2.4
 */
namespace suda\tool;

use ArrayIterator;
use IteratorAggregate;
use JsonSerializable;

/**
 * 值迭代器
 */
class Value implements IteratorAggregate, JsonSerializable
{
    protected $var;

    public function __construct($var=[])
    {
        $this->var=$var;
    }

    public function __get(string $name)
    {
        return  $this->var[$name] ?? null;
    }

    public function __set(string $name, $value)
    {
        return $this->var[$name]=$value;
    }

    public function __isset(string $name)
    {
        return array_key_exists($name, $this->var);
    }

    public function __call(string $name, $args)
    {
        return $this->var[$name] ?? $args[0] ?? null;
    }

    public function getIterator()
    {
        if (func_num_args() === 1) {
            return $this->var[__FUNCTION__] ?? func_get_arg(0);
        }
        return new ArrayIterator($this->var);
    }

    public function jsonSerialize()
    {
        if (func_num_args() === 1) {
            return $this->var[__FUNCTION__] ?? func_get_arg(0);
        }
        return $this->var;
    }

    public function __toString()
    {
        return json_encode($this->var);
    }
}
