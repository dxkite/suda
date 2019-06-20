<?php


namespace suda\tool;


use ReflectionClass;
use ReflectionException;

class JsonObject implements \JsonSerializable
{
    protected $data;

    /**
     * JsonObject constructor.
     * @param mixed $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * 判断是否为某种类型
     *
     * @param mixed $data
     * @param string $type
     * @return boolean
     */
    public static function isTypeOf($data, string $type) : bool
    {
        if (is_object($data)
            && !in_array($type, ['boolean', 'bool', 'integer', 'int', 'float', 'double', 'string','array','NULL'])) {
            try {
                $class = new ReflectionClass($data);
                $typeRef = new ReflectionClass($type);
                if ($typeRef->isInterface()) {
                    return $class->implementsInterface($type);
                } else {
                    return $class->isSubclassOf($type) || $typeRef->isInstance($data);
                }
            } catch (ReflectionException $e) {
                return false;
            }
        } else {
            return gettype($data) === static::phpTypeAlias($type);
        }
    }

    protected static function phpTypeAlias(string $type):string
    {
        if ($type === 'bool') {
            return 'boolean';
        }
        if ($type === 'int') {
            return 'integer';
        }
        if ($type === 'float') {
            return 'double';
        }
        return $type;
    }

    public function jsonSerialize()
    {
        return $this->data;
    }
}