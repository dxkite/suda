<?php
namespace suda\orm\struct;

use ReflectionClass;
use ReflectionProperty;
use suda\orm\TableStruct;
use suda\orm\struct\Field;
use suda\orm\struct\TableStructBuilder;
use suda\orm\struct\FieldModifierParser;

/**
 * 从类的类注释构建对象
 */
class TableClassStructBuilder extends TableStructBuilder
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

    /**
     * 类文档
     *
     * @var string
     */
    protected $classDoc;

    public function __construct(string $object)
    {
        $this->object = $object;
        $this->reflectObject = new ReflectionClass($object);
        $this->classDoc = $this->reflectObject->getDocComment() ?: '';
    }


    /**
     * 创建表结构
     *
     * @return TableStruct
     */
    public function createStruct():TableStruct
    {
        if (($match = static::readClassDocField($this->classDoc)) !== null) {
            return $this->createClassTableStruct($match);
        }
        return parent::createStruct();
    }

    /**
     * 判断类文档注释是否包含字段
     *
     * @param strring $classDoc
     * @return array|null
     */
    public static function readClassDocField(string $classDoc):?array
    {
        if (preg_match_all('/@field\s+(\w+)\s+(\w+)(?:\((.+?)\))?\s+(.+)?$/im', $classDoc, $match)) {
            return $match;
        }
        return null;
    }

 
    /**
     * 创建表结构
     *
     * @param array $fields
     * @return \suda\orm\TableStruct
     */
    protected function createClassTableStruct(array $fields): TableStruct
    {
        $name = $this->getName();
        $struct = new TableStruct($name);
        foreach ($fields[0] as $index => $value) {
            $match = \array_column($fields, $index);
            list($comment, $field, $type, $length, $modifier) = $match;
            $fieldObj = $this->createClassField($name, trim($field), trim($type), static::parseLength($length), trim($modifier));
            $struct->addField($fieldObj);
        }
        return $struct;
    }

    /**
     * 创建字段
     *
     * @param string $tableName
     * @param string $name
     * @param string $type
     * @param string|array|null $length
     * @param string $modifier
     * @return \suda\orm\struct\Field
     */
    protected function createClassField(string $tableName, string $name, string $type, $length, string $modifier): Field
    {
        $parser = new FieldModifierParser;
        $field = new Field($tableName, $name, $type, $length);
        $parser->parse($modifier)->modify($field);
        return $field;
    }
}
