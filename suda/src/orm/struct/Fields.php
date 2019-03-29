<?php
namespace suda\orm\struct;

use IteratorAggregate;
use suda\orm\struct\Field;

class Fields implements IteratorAggregate
{
    /**
     * 数据表名
     *
     * @var string
     */
    protected $name;

    /**
     * 字段集合
     *
     * @var Field[]
     */
    protected $fields;

    /**
     * 创建字段集合
     *
     * @param string $table
     */
    public function __construct(string $table)
    {
        $this->name = $table;
    }

    /**
     * 新建表列
     *
     * @param string $name
     * @param string $type
     * @param int $length
     * @return Field
     */
    public function field(string $name, string $type, int $length = null)
    {
        return $this->fields[$name] ?? $this->fields[$name] = ($length?new Field($this->name, $name, $type, $length):new Field($this->name, $name, $type));
    }

    public function newField(string $name, string $type, int $length = null)
    {
        return $this->fields[$name] ?? $this->fields[$name] = ($length?new Field($this->name, $name, $type, $length):new Field($this->name, $name, $type));
    }

    public function getField(string $name)
    {
        return $this->fields[$name] ?? null;
    }

    public function hasField(string $name)
    {
        return array_key_exists($name, $this->fields);
    }

    public function addField(Field $field)
    {
        if ($field->getTableName() != $this->name) {
            return;
        }
        $name = $field->getName();
        $this->fields[$name] = $field;
    }
    
    public function getFieldsName()
    {
        return array_keys($this->fields);
    }

    /**
     * Get the value of name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the value of fields
     */
    public function all()
    {
        return $this->fields;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->fields);
    }
}
