<?php
namespace suda\orm\struct;

use function array_column;
use function preg_match_all;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
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
     * @param TableStruct $struct
     * @throws ReflectionException
     */
    public function __construct(string $object, TableStruct $struct)
    {
        $this->struct = $struct;
        parent::__construct($object);
    }

    /**
     * 处理输入字段名
     * @param string $name
     * @return string
     */
    public function inputName(string $name):string
    {
        return $this->struct->inputName($name);
    }

    /**
     * 处理输出字段名
     *
     * @param string $name
     * @return mixed
     */
    public function outputName(string $name):string
    {
        return $this->struct->outputName($name);
    }

    /**
     * 创建处理集合
     *
     * @param string $object
     * @return void
     * @throws ReflectionException
     */
    protected function prepareProcessorSet(string $object)
    {
        $reflectObject = new ReflectionClass($object);
        $classDoc = is_string($reflectObject->getDocComment())?$reflectObject->getDocComment():'';
        $field = TableClassStructBuilder::readClassDocField($classDoc);
        if ($field !== null) {
            $this->createProccorFromStruct();
        } else {
            $this->createProccorFromClass($reflectObject);
        }
        $this->rewriteFromClassDoc($classDoc);
    }

    protected function createProccorFromStruct()
    {
        $this->processor = [];
        $fields = $this->struct;
        foreach ($fields as $key => $value) {
            $this->processor[$key] = ObjectMiddleware::RAW;
        }
    }

    protected function rewriteFromClassDoc(string $classDoc)
    {
        if (preg_match_all('/@field-(serialize|json)\s+(\w+)/i', $classDoc, $matchs) > 0) {
            foreach ($matchs[0] as $index => $value) {
                $match = array_column($matchs, $index);
                list($comment, $type, $name) = $match;
                $this->processor[$name] = strtolower($type) === 'json' ? ObjectMiddleware::JSON : ObjectMiddleware::SERIALIZE;
            }
        }
    }

    protected function createProccorFromClass(ReflectionClass $reflectObject)
    {
        $this->processor = [];
        foreach ($reflectObject->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE) as $property) {
            $name = $this->inputName($property->getName());
            $this->processor[$name] = $this->getProcessorType($property);
        }
    }
}
