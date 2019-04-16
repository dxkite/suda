<?php
namespace suda\orm\middleware;

use ReflectionClass;
use ReflectionProperty;
use suda\orm\TableStruct;
use suda\orm\middleware\NullMiddleware;
use suda\orm\struct\TableStructBuilder;

/**
 * 结构中间件
 */
class ObjectMiddleware extends NullMiddleware
{
    /**
     * 处理方式
     *
     * @var array
     */
    protected $processor;

    /**
     * 数据对象
     *
     * @var string
     */
    protected $object;

    /**
     * 字段映射处理
     *
     * @var array
     */
    protected $nameAlias;

    const RAW = 0;
    const SERIALIZE = 2;

    /**
     * 创建中间件
     *
     * @param string $object
     */
    public function __construct(string $object)
    {
        $this->prepareProcessorSet($object);
        $this->object = $object;
    }

    /**
     * 处理输入数据
     *
     * @param string $name
     * @param mixed $data
     * @return mixed
     */
    public function input(string $name, $data)
    {
        if ($this->processor[$name] === ObjectMiddleware::SERIALIZE) {
            return \serialize($data);
        }
        return $data;
    }

    /**
     * 处理输出数据
     *
     * @param string $name
     * @param mixed $data
     * @return mixed
     */
    public function output(string $name, $data)
    {
        if ($this->processor[$name] === ObjectMiddleware::SERIALIZE) {
            return \unserialize($data) ?: null;
        }
        return $data;
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
            $name = $this->getFieldName($property);
            $this->processor[$name] = $this->getProcessorType($property);
            $this->nameAlias[$property->getName()] = $name;
        }
    }

    /**
     * 获取字段名
     *
     * @param ReflectionProperty $property
     * @return string
     */
    protected function getFieldName(ReflectionProperty $property)
    {
        return $property->getName();
    }

    /**
     * 处理输入字段名
     */
    public function inputName(string $name):string
    {
        if (\array_key_exists($name, $this->nameAlias)) {
            return $this->nameAlias[$name];
        }
        return $name;
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
        if ($key = \array_search($name, $this->nameAlias)) {
            return $key;
        }
        return $name;
    }

    /**
     * 获取处理方式
     *
     * @param ReflectionProperty $property
     * @return integer
     */
    protected function getProcessorType(ReflectionProperty $property):int
    {
        if ($doc = $property->getDocComment()) {
            if (is_string($doc) && preg_match('/@var\s+(\w+)/i', $doc, $match)) {
                $type = \strtolower($match[1]);
                if (\in_array($type, ['boolean', 'bool', 'integer', 'int' , 'float' , 'double', 'string'])) {
                    return ObjectMiddleware::RAW;
                }
            }
        }
        return ObjectMiddleware::SERIALIZE;
    }
}
