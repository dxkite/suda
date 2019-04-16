<?php
namespace suda\orm\struct;

trait ArrayDataTrait  
{
    use PropertyDataTrait;
    /**
     * 表数据
     *
     * @var array
     */
    protected $data = [];

    
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
    public function __set(string $name, $value)
    {
        $this->assertFieldName($name);
        $this->data[$name] = $value;
    }

    
    
    /**
     * 获取参数值
     *
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        $this->assertFieldName($name);
        return $this->data[$name] ?? null;
    }

    /**
     * 判断是否设置
     *
     * @param string $name
     * @return boolean
     */
    public function __isset(string $name)
    {
        $this->assertFieldName($name);
        return array_key_exists($name, $this->data);
    }

    /**
     * 取消设置值
     *
     * @param string $name
     */
    public function __unset(string $name)
    {
        $this->assertFieldName($name);
        unset($this->data[$name]);
    }

    /**
     * 断言字段
     *
     * @param string $name
     * @return void
     */
    protected function assertFieldName(string $name)
    {
        if ($this->checkFieldExist($name) === false) {
            throw new InvalidArgumentException(sprintf('[%s] has no attribute %s', static::class, $name), 0);
        }
    }

    /**
     * 检查字段是否存在
     *
     * @param string $name
     * @return boolean
     */
    public function checkFieldExist(string $name)
    {
        return true;
    }
}
