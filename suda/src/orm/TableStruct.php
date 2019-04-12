<?php
namespace suda\orm;

use Countable;
use ArrayAccess;
use ArrayIterator;
use JsonSerializable;
use IteratorAggregate;
use suda\orm\struct\Field;
use suda\orm\struct\Fields;
use InvalidArgumentException;

class TableStruct implements ArrayAccess, IteratorAggregate, Countable, JsonSerializable
{
    /**
     * 数据表名
     *
     * @var string
     */
    protected $name;
    
    /**
     * 数据表列
     *
     * @var Fields
     */
    protected $fields;

    /**
     * 表数据
     *
     * @var array
     */
    protected $data = [];

    /**
     * 创建表结构
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
        $this->fields = new Fields($name);
    }

    /**
     * 添加表结构字段
     *
     * @param array|Field $fields
     * @return self
     */
    public function fields($fields)
    {
        if (!is_array($fields) && $fields instanceof Field) {
            $fields = func_get_args();
        }
        foreach ($fields as $field) {
            $this->fields->addField($field);
        }
        return $this;
    }

    public function field(string $name, string $type, int $length = 0)
    {
        return $this->fields->newField($name, $type, $length);
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
        $struct->fields = $this->fields;
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
     * 获取迭代器
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->data);
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
     * Get 数据表列
     *
     * @return  Fields
     */
    public function getFields():Fields
    {
        return $this->fields;
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
    
    /**
     * 设置值
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set(string $name, $value)
    {
        if ($this->fields->hasField($name)) {
            $this->data[$name] = $value;
            return;
        }
        throw new InvalidArgumentException(sprintf('TableStruct[%s] has no attribute %s', $this->name, $name), 1);
    }

    /**
     * 获取参数值
     *
     * @param string $name
     * @return mixed
     */
    public function __get(string $name) {
        if ($this->fields->hasField($name)) {
            return $this->data[$name] ?? null;
        }
        throw new InvalidArgumentException(sprintf('TableStruct[%s] has no attribute %s', $this->name, $name), 2);
    }

    /**
     * 判断是否设置
     *
     * @param string $name
     * @return boolean
     */
    public function __isset(string $name) {
        if ($this->fields->hasField($name) === false) {
            throw new InvalidArgumentException(sprintf('TableStruct[%s] has no attribute %s', $this->name, $name), 3);
        }
        return array_key_exists($name, $this->data);
    }

    /**
     * 取消设置值
     *
     * @param string $name
     */
    public function __unset(string $name) {
        if ($this->fields->hasField($name) === false) {
            throw new InvalidArgumentException(sprintf('TableStruct[%s] has no attribute %s', $this->name, $name), 4);
        }
        unset($this->data[$name]);
    }

    /**
     * 获取序列化对象
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->data;
    }
}
