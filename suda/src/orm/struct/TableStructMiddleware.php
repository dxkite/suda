<?php
namespace suda\orm\struct;

use ReflectionClass;
use ReflectionProperty;
use suda\orm\TableStruct;
use suda\orm\middleware\ObjectMiddleware;

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
        $this->processor = [];
        foreach ($reflectObject->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE) as $property) {
            $name = $this->inputName($property->getName());
            $this->processor[$name] = $this->getProcessorType($property);
        }
    }
}
