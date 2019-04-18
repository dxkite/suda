<?php
namespace suda\orm\struct;

use ArrayIterator;
use InvalidArgumentException;
use suda\orm\struct\MagicArrayAccessTrait;

trait ArrayDataTrait  
{
    use MagicArrayAccessTrait;
    use SimpleJsonDataTrait;

    /**
     * 表数据
     *
     * @var array
     */
    protected $data = [];

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
        return strlen($name) > 0;
    }

    /**
     * 获取迭代器
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->data);
    }
    
    /**
     * 转换成原始数组
     *
     * @return array
     */
    public function toArray():array
    {
        return $this->data;
    }

    /**
     * 计数
     *
     * @return int
     */
    public function count()
    {
        return count($this->data);
    }

    public function getJsonData()
    {
        return $this->data;
    }
}
