<?php
namespace suda\database\struct;

/**
 * Trait MagicArrayAccessTrait
 * @package suda\database\struct
 */
trait MagicArrayAccessTrait
{
    /**
     * @param $offset
     * @param $value
     */
    public function offsetSet($offset, $value)
    {
        $this->__set($offset, $value);
    }

    /**
     * @param $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->__isset($offset);
    }

    /**
     * @param $offset
     */
    public function offsetUnset($offset)
    {
        $this->__unset($offset);
    }

    /**
     * @param $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    /**
     * 设置值
     *
     * @param string $name
     * @param mixed $value
     */
    abstract public function __set(string $name, $value);
    
    /**
     * 获取参数值
     *
     * @param string $name
     * @return mixed
     */
    abstract public function __get(string $name);

    /**
     * 判断是否设置
     *
     * @param string $name
     * @return boolean
     */
    abstract public function __isset(string $name);

    /**
     * 取消设置值
     *
     * @param string $name
     */
    abstract public function __unset(string $name);
}
