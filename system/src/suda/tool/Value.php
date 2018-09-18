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
     * @return mixed
     */
    public function _getVar()
    {
        return $this->var;
    }

    /**
     * @param mixed $var
     */
    public function _setVar($var)
    {
        $this->var = $var;
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
        return isset($this->var[$name]);
    }

    /**
     * @param string $name
     * @param $args
     * @return mixed|string
     */
    public function __call(string $name, $args)
    {
        // NOTICE
        $fmt= $this->var[$name] ?? $args[0] ?? null;
        if (count($args)>1) {
            $args[0]=$fmt;
            return call_user_func_array('sprintf', $args);
        }
        return $fmt;
    }
    /// 迭代器扩展
    public function rewind()
    {
        reset($this->var);
    }

    public function current()
    {
        return  current($this->var);
    }

    public function key()
    {
        return key($this->var);
    }

    public function next()
    {
        return next($this->var);
    }

    public function valid()
    {
        return $this->current() !== false;
    }

    public function jsonSerialize()
    {
        return $this->var;
    }

    public function __toString()
    {
        return json_encode($this);
    }
}
