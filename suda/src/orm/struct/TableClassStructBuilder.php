<?php
namespace suda\orm\struct;

use function array_column;
use ReflectionClass;
use ReflectionException;
use suda\orm\struct\TableStruct;

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

    /**
     * TableClassStructBuilder constructor.
     * @param string $object
     * @throws ReflectionException
     */
    public function __construct(string $object)
    {
        parent::__construct($object);
        $this->classDoc = is_string($this->reflectObject->getDocComment())?$this->reflectObject->getDocComment():'';
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
     * @param string $classDoc
     * @return array|null
     */
    public static function readClassDocField(string $classDoc):?array
    {
        if (preg_match_all(
            '/^.+\s+\@field(?:\-(?:serialize|json))?\s+(\w+)\s+(\w+)(?:\((.+?)\))?(.*?)$/im',
            $classDoc,
            $match
        ) > 0) {
            return is_array($match)?$match:null;
        }
        return null;
    }


    /**
     * 创建表结构
     *
     * @param array $fields
     * @return TableStruct
     */
    protected function createClassTableStruct(array $fields): TableStruct
    {
        $name = $this->getName();
        $struct = new TableStruct($name);
        foreach ($fields[0] as $index => $value) {
            $match = array_column($fields, $index);
            list($comment, $field, $type, $length, $modifier) = $match;
            $fieldObj = $this->createClassField(
                $name,
                trim($field),
                trim($type),
                static::parseLength($length),
                trim($modifier)
            );
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
     * @return Field
     */
    protected function createClassField(string $tableName, string $name, string $type, $length, string $modifier): Field
    {
        $parser = new FieldModifierParser;
        $field = new Field($tableName, $name, $type, $length);
        $parser->parse($modifier)->modify($field);
        return $field;
    }
}
