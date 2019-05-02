<?php
namespace suda\orm\struct;

use function array_key_exists;
use function array_search;
use ArrayIterator;
use IteratorAggregate;
use Traversable;

/**
 * Class Fields
 * @package suda\orm\struct
 */
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
     * 键值对映射
     *
     * @var array
     */
    protected $alias;

    /**
     * 创建字段集合
     *
     * @param string $table
     */
    public function __construct(string $table)
    {
        $this->name = $table;
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
        return $this->fields[$name] ?? $this->fields[$name] = ($length?new Field($this->name, $name, $type, $length):new Field($this->name, $name, $type));
    }

    /**
     * @param string $name
     * @param string $type
     * @param null $length
     * @return mixed|Field
     */
    public function newField(string $name, string $type, $length = null)
    {
        return $this->fields[$name] ?? $this->fields[$name] = ($length?new Field($this->name, $name, $type, $length):new Field($this->name, $name, $type));
    }

    /**
     * @param string $name
     * @return mixed|Field|null
     */
    public function getField(string $name)
    {
        return $this->fields[$name] ?? null;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasField(string $name)
    {
        return array_key_exists($name, $this->fields);
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
    public function outputName(string $name):string {
        if (array_key_exists($name, $this->alias)) {
            return $this->alias[$name];
        }
        return $name;
    }

    /**
     * @param string $name
     * @return string
     */
    public function inputName(string $name):string {
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
}
