<?php
namespace suda\orm\struct;

trait MagicArrayAccessTrait
{
    public function offsetSet($offset, $value)
    {
        $this->__set($offset, $value);
    }

    public function offsetExists($offset)
    {
        return $this->__isset($offset);
    }

    public function offsetUnset($offset)
    {
        $this->__unset($offset);
    }

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
