<?php
namespace suda\database\struct;

trait PropertyDataTrait
{
    use MagicArrayAccessTrait;

    /**
     * 设置值
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set(string $name, $value)
    {
        $this->$name = $value;
    }

    /**
     * 获取参数值
     *
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->$name;
    }

    /**
     * 判断是否设置
     *
     * @param string $name
     * @return boolean
     */
    public function __isset(string $name)
    {
        return isset($this->$name);
    }

    /**
     * 取消设置值
     *
     * @param string $name
     */
    public function __unset(string $name)
    {
        unset($this->$name);
    }
}
