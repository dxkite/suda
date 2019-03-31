<?php
namespace suda\orm;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use suda\orm\struct\Field;
use suda\orm\struct\Fields;

class TableStruct implements ArrayAccess, IteratorAggregate
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

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->fields = new Fields($name);
    }

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
        return $struct;
    }

    public function offsetSet($offset, $value)
    {
        if (null === $offset) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }

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

    public function toArray():array
    {
        return $this->data;
    }
}
