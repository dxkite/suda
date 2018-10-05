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

/**
 * 普通通用值
 *
 * 通用指可以使用迭代器和JSON化成字符串
 * 并且包含魔术变量用于处理其值
 *
 * @package suda\tool
 */
class Value implements \Iterator, \JsonSerializable
{
    /**
     * @var
     */
    protected $var;
    /**
     * Value constructor.
     * @param $var
     */
    public function __construct($var=[])
    {
        $this->var=$var;
    }

    /**
     * @param string $name
     * @return string
     */
    public function __get(string $name)
    {
        return  $this->var[$name] ?? null;
    }

    /**
     * @param string $name
     * @param $value
     * @return mixed
     */
    public function __set(string $name, $value)
    {
        return $this->var[$name]=$value;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset(string $name)
    {
        return array_key_exists($name, $this->var);
    }

    /**
     * @param string $name
     * @param $args
     * @return mixed|string
     */
    public function __call(string $name, $args)
    {
        return $this->var[$name] ?? $args[0] ?? null;
    }


    // 迭代器扩展
    public function rewind($default=null)
    {
        $name = __FUNCTION__;
        if (!is_null($default)) {
            return $this->var[$name] ?? $default ?? null;
        }
        reset($this->var);
    }

    public function current($default=null)
    {
        $name = __FUNCTION__;
        if (!is_null($default)) {
            return $this->var[$name] ?? $default ?? null;
        }
        return current($this->var);
    }

    public function key($default=null)
    {
        $name = __FUNCTION__;
        if (!is_null($default)) {
            return $this->var[$name] ?? $default ?? null;
        }
        return key($this->var);
    }

    public function next($default=null)
    {
        $name = __FUNCTION__;
        if (!is_null($default)) {
            return $this->var[$name] ?? $default ?? null;
        }
        return next($this->var);
    }

    public function valid($default=null)
    {
        $name = __FUNCTION__;
        if (!is_null($default)) {
            return $this->var[$name] ?? $default ?? null;
        }
        return $this->current() !== false;
    }

    public function jsonSerialize($default=null)
    {
        $name = __FUNCTION__;
        if (!is_null($default)) {
            return $this->var[$name] ?? $default ?? null;
        }
        return $this->var;
    }

    public function __toString()
    {
        return json_encode($this);
    }
}
