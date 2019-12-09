<?php

namespace suda\database\struct;

use suda\database\connection\Connection;
use function array_key_exists;
use function array_search;
use ArrayIterator;
use IteratorAggregate;
use Traversable;

/**
 * Class TableStruct
 * @package suda\database\struct
 */
class TableStruct implements IteratorAggregate
{
    /**
     * 数据表名
     *
     * @var string
     */
    protected $name;

    /**
     * 是否为原始表名
     *
     * @var bool
     */
    protected $isRawName;

    /**
     * 字段集合
     *
     * @var Field[]
     */
    protected $fields;

    /**
     * 键值对映射
     *
     * @var array
     */
    protected $alias;

    /**
     * 创建字段集合
     *
     * @param string $name
     * @param bool $raw
     */
    public function __construct(string $name, bool $raw = false)
    {
        $this->name = $name;
        $this->isRawName = $raw;
        $this->fields = [];
    }

    /**
     * 新建表列
     *
     * @param string $name
     * @param string $type
     * @param int|array $length
     * @return Field
     */
    public function field(string $name, string $type, $length = null)
    {
        if ($length === null) {
            $this->fields[$name] = new Field($this->name, $name, $type);
        } else {
            $this->fields[$name] = new Field($this->name, $name, $type, $length);
        }
        return $this->fields[$name];
    }

    /**
     * @param string $name
     * @param string $type
     * @param mixed $length
     * @return Field
     */
    public function newField(string $name, string $type, $length = null)
    {
        return $this->field($name, $type, $length);
    }

    /**
     * @param string $name
     * @return Field|null
     */
    public function getField(string $name)
    {
        return $this->fields[$name] ?? null;
    }

    /**
     * 添加表结构字段
     *
     * @param array|Field $fields
     * @return $this
     */
    public function fields($fields)
    {
        if (!is_array($fields) && $fields instanceof Field) {
            $fields = func_get_args();
        }
        foreach ($fields as $field) {
            $this->addField($field);
        }
        return $this;
    }

    /**
     * @param string $name
     * @param string|null $type
     * @param int|string|array|null $length
     * @return bool
     */
    public function hasField(string $name, string $type = null, $length = null)
    {
        // 检查字段
        if (array_key_exists($name, $this->fields) === false) {
            return false;
        }
        // 检查类型
        if ($type !== null
            && $this->fields[$name]->equals(new Field($this->getName(), $name, $type, $length)) === false) {
            return false;
        }
        return true;
    }

    /**
     * @param Field $field
     */
    public function addField(Field $field)
    {
        if ($field->getTableName() != $this->name) {
            return;
        }
        $name = $field->getName();
        $this->fields[$name] = $field;
        $this->alias[$name] = $field->getAlias();
    }

    /**
     * @param string $name
     * @return string
     */
    public function outputName(string $name): string
    {
        if (array_key_exists($name, $this->alias)) {
            return $this->alias[$name];
        }
        return $name;
    }

    /**
     * @param string $name
     * @return string
     */
    public function inputName(string $name): string
    {
        if ($key = array_search($name, $this->alias)) {
            return $key;
        }
        return $name;
    }

    /**
     * @return array
     */
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
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param bool $isRawName
     */
    public function setIsRawName(bool $isRawName): void
    {
        $this->isRawName = $isRawName;
    }

    /**
     * Get the value of fields
     */
    public function all()
    {
        return $this->fields;
    }

    /**
     * @return ArrayIterator|Traversable
     */
    public function getIterator()
    {
        return new ArrayIterator($this->fields);
    }

    /**
     * @param TableStruct $some
     * @param TableStruct $struct
     * @return bool
     */
    public static function isSubOf(TableStruct $some, TableStruct $struct)
    {
        foreach ($struct->fields as $field) {
            $name = $field->getName();
            if (array_key_exists($name, $some->fields) === false
                || $some->fields[$name]->equals($field) === false) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param Connection $connection
     * @return string
     */
    public function getRealTableName(Connection $connection): string  {
        if ($this->isRawName) {
            return $this->getName();
        }
        return $connection->rawTableName($this->getName());
    }
}
