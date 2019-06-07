<?php
namespace suda\database;

use suda\database\struct\Field;
use suda\database\struct\TableStruct;
use InvalidArgumentException;
use suda\database\struct\ArrayDataTrait;
use suda\database\struct\ArrayDataInterface;

class TableData implements ArrayDataInterface
{
    use ArrayDataTrait;
    /**
     * 数据表名
     *
     * @var string
     */
    protected $name;
    
    /**
     * 数据表列
     *
     * @var TableStruct
     */
    protected $struct;

    /**
     * 创建表结构
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
        $this->struct = new TableStruct($name);
    }

    public function createAll(array $data)
    {
        foreach ($data as $index => $row) {
            $data[$index] = $this->createOne($row);
        }
        return $data;
    }

    public function createOne(array $data)
    {
        $struct = new self($this->name);
        $struct->data = $data;
        $struct->struct = $this->struct;
        return $struct;
    }

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
     * Get 数据表名
     *
     * @return  string
     */
    public function getName():string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
        $this->struct->setName($name);
    }

    /**
     * Get 数据表列
     *
     * @return  TableStruct
     */
    public function getStruct():TableStruct
    {
        return $this->struct;
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

    protected function assertFieldName(string $name)
    {
        if ($this->struct->hasField($name) === false) {
            throw new InvalidArgumentException(sprintf('TableStruct[%s] has no attribute %s', $this->name, $name), 0);
        }
    }
}
