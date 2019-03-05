<?php
namespace suda\orm\struct;

use suda\orm\struct\Field;

class Fields
{
    protected $name;
    protected $fields;

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
}
