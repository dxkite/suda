<?php
namespace suda\orm;

use ReflectionClass;
use ReflectionProperty;
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
        foreach ($this->reflectObject->getProperties() as $property) {
            $field = $this->createField($name, $property);
            $struct->addField($field);
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
        return $this->createName($this->reflectObject->getShortName());
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
        $name = $this->getFieldName($property);
        list($type, $length, $modifier) = $this->getFieldType($property);
        return new Field($tableName, $name, $type, $length);
    }

    /**
     * 转换名称
     *
     * @param string $name
     * @return string
     */
    protected function createName(string $name):string
    {
        $name = preg_replace('/([A-Z]+)/', '_$1', $name);
        $name = \preg_replace('/_+/', '_', $name);
        $name = trim($name, '_');
        return \strtolower($name);
    }

    /**
     * 获取字段名
     *
     * @param \ReflectionProperty $property
     * @return string
     */
    protected function getFieldName(ReflectionProperty $property)
    {
        if ($doc = $property->getDocComment()) {
            if (is_string($doc) && preg_match('/@field-?name\s+(\w+)/i', $doc, $match)) {
                return $match[1];
            }
        }
        return $this->createName($property->getName());
    }

    /**
     * 获取字段类型
     *
     * @param \ReflectionProperty $property
     * @return array
     */
    protected function getFieldType(ReflectionProperty $property)
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
}
