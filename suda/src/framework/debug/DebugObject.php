<?php


namespace suda\framework\debug;

use JsonSerializable;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

class DebugObject implements JsonSerializable
{
    /**
     * @var DebugObjectContext
     */
    protected $context;

    /**
     * @var mixed
     */
    protected $value;

    public function __construct($value, DebugObjectContext $context = null)
    {
        $this->context = $context ?: new DebugObjectContext();
        $this->value = $value;
    }

    /**
     * @param array $value
     * @return array
     */
    protected function parseArray(array $value)
    {
        foreach ($value as $key => $val) {
            $value[$key] = new DebugObject($val, $this->context);
        }
        return $value;
    }

    /**
     * @param DebugObject $object
     * @return array
     */
    protected function parseObject($object)
    {
        $objectHash = spl_object_hash($object);
        if ($this->context->isObjectExported($objectHash)) {
            return ['_type' => get_class($object), '_hash' => $objectHash];
        }
        $this->context->setObjectIsExported($objectHash);
        return [
            '_type' => get_class($object),
            '_hash' => $objectHash,
            '_properties' => $this->getObjectProp($object)
        ];
    }

    /**
     * @param $object
     * @return string|array
     */
    protected function getObjectProp($object)
    {
        try {
            $oR = new ReflectionClass($object);
            $props = $oR->getProperties(
                ReflectionProperty::IS_PUBLIC
                | ReflectionProperty::IS_PROTECTED
                | ReflectionProperty::IS_PRIVATE
            );
            $exported = [];
            foreach ($props as $value) {
                $name = dechex($value->getModifiers()) . '$' . $value->getName();
                $value->setAccessible(true);
                $exported[$name] = new DebugObject($value->getValue($object), $this->context);
            }
            return $exported;
        } catch (ReflectionException $e) {
            return 'Err:' . $e->getMessage();
        }
    }



    public function jsonSerialize()
    {
        if (is_object($this->value)) {
            return $this->parseObject($this->value);
        }
        if (is_array($this->value)) {
            return $this->parseArray($this->value);
        }
        if (is_resource($this->value)) {
            return ['_resource' => get_resource_type($this->value)];
        }
        return $this->value;
    }
}
