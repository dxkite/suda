<?php
namespace suda\orm\struct;

use ReflectionClass;
use ReflectionProperty;
use suda\orm\TableStruct;
use suda\orm\middleware\ObjectMiddleware;
use suda\orm\struct\TableClassStructBuilder;

/**
 * 结构中间件
 */
class TableStructMiddleware extends ObjectMiddleware
{
    /**
     * 数据结构
     *
     * @var TableStruct
     */
    protected $struct;

    /**
     * 创建中间件
     *
     * @param string $object
     */
    public function __construct(string $object, TableStruct $struct)
    {
        $this->object = $object;
        $this->struct = $struct;
        $this->prepareProcessorSet($object);
    }

    /**
     * 处理输入字段名
     */
    public function inputName(string $name):string
    {
        return $this->struct->getFields()->inputName($name);
    }

    /**
     * 处理输出字段名
     *
     * @param string $name
     * @param mixed $data
     * @return mixed
     */
    public function outputName(string $name):string
    {
        return $this->struct->getFields()->outputName($name);
    }
    
    /**
     * 创建处理集合
     *
     * @param string $object
     * @return void
     */
    protected function prepareProcessorSet(string $object)
    {
        $reflectObject = new ReflectionClass($object);
        $classDoc = $reflectObject->getDocComment()?:'';
        $field = TableClassStructBuilder::readClassDocField($classDoc);
        if ($field !== null) {
            $this->createProccorFromStruct();
        } else {
            $this->createProccorFromClass();
        }
        $this->rewriteFromClassDoc($classDoc);
    }

    protected function createProccorFromStruct()
    {
        $this->processor = [];
        $fields = $this->struct->getFields();
        foreach ($fields as $key => $value) {
            $this->processor[$key] = ObjectMiddleware::RAW;
        }
    }

    protected function rewriteFromClassDoc(string $classDoc)
    {
        if (\preg_match_all('/@field-serialize\s+(\w+)/i', $classDoc, $matchs)) {
            foreach ($matchs[0] as $index => $value) {
                $match = \array_column($matchs, $index);
                list($comment, $name) = $match;
                $this->processor[$name] = ObjectMiddleware::SERIALIZE;
            }
        }
    }

    protected function createProccorFromClass()
    {
        $this->processor = [];
        foreach ($reflectObject->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE) as $property) {
            $name = $this->inputName($property->getName());
            $this->processor[$name] = $this->getProcessorType($property);
        }
    }
}
