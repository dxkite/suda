<?php
namespace suda\orm\struct;

use ReflectionClass;
use ReflectionProperty;
use suda\orm\TableStruct;
use suda\orm\struct\Field;

class TableStructBuilder
{
    /**
     * 解析对象
     *
     * @var string
     */
    protected $object;


    /**
     * 反射对象
     *
     * @var ReflectionClass
     */
    protected $reflectObject;

    public function __construct(string $object)
    {
        $this->object = $object;
        $this->reflectObject = new ReflectionClass($object);
    }


    /**
     * 创建表结构
     *
     * @return TableStruct
     */
    public function createStruct():TableStruct
    {
        $name = $this->getName();
        $struct = new TableStruct($name);
        foreach ($this->reflectObject->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE) as $property) {
            if ($this->isTableField($property)) {
                $field = $this->createField($name, $property);
                $struct->addField($field);
            }
        }
        return $struct;
    }

    /**
     * 获取表名
     *
     * @return string
     */
    protected function getName()
    {
        if ($doc = $this->reflectObject->getDocComment()) {
            if (is_string($doc) && \preg_match('/\@table\s+(\w+)/', $doc, $match)) {
                return $match[1];
            }
        }
        return static::createName($this->reflectObject->getShortName());
    }

    /**
     * 创建字段
     *
     * @param string $tableName
     * @param \ReflectionProperty $property
     * @return \suda\orm\struct\Field
     */
    protected function createField(string $tableName, ReflectionProperty $property): Field
    {
        $name = static::getFieldName($property);
        list($type, $length, $modifier) = static::getFieldType($property);
        $field = new Field($tableName, $name, $type, $length);
        $field->alias($property->getName());
        $parser = new FieldModifierParser;
        $parser->parse($modifier)->modify($field);
        return $field;
    }

    /**
     * 转换名称
     *
     * @param string $name
     * @return string
     */
    public static function createName(string $name):string
    {
        $name = preg_replace('/([A-Z]+)/', '_$1', $name);
        $name = \preg_replace('/_+/', '_', $name);
        $name = trim($name, '_');
        return \strtolower($name);
    }

    /**
     * 获取表字段名
     *
     * @param string $object
     * @param string $name
     * @return string
     */
    public static function getTableFieldName(string $object, string $name) {
        $property = new ReflectionProperty($object, $name);
        return static::getFieldName($property);
    }

    /**
     * 获取字段名
     *
     * @param \ReflectionProperty $property
     * @return string
     */
    public static function getFieldName(ReflectionProperty $property)
    {
        if ($doc = $property->getDocComment()) {
            if (is_string($doc) && preg_match('/@field-?name\s+(\w+)/i', $doc, $match)) {
                return $match[1];
            }
        }
        return $property->getName();
    }

    /**
     * 获取字段类型
     *
     * @param \ReflectionProperty $property
     * @return array|null
     */
    public static function getFieldType(ReflectionProperty $property)
    {
        if ($doc = $property->getDocComment()) {
            if (is_string($doc) && preg_match('/@field\s+(\w+)(?:\((.+?)\))?\s+(.+)?$/im', $doc, $match)) {
                $type = strtoupper($match[1]);
                $length = $match[2] ?? '';
                if (strlen($length)) {
                    $length = \explode(',', $length);
                    if (count($length) === 1) {
                        $length = $length[0];
                    }
                } else {
                    $length = null;
                }
                return [$type, $length , trim($match[3] ?? '')];
            }
        }
        return ['text', null, ''];
    }

    /**
     * 检查是否为数据库字段
     *
     * @param \ReflectionProperty $property
     * @return boolean
     */
    public static function isTableField(ReflectionProperty $property) {
        if ($doc = $property->getDocComment()) {
            if (is_string($doc) && stripos($doc, '@field')) {
                return true;
            }
        }
        return false;
    }
}
