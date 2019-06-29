<?php
namespace suda\framework\response;

use ReflectionException;
use SplFileObject;
use ReflectionClass;
use JsonSerializable;
use suda\framework\exception\NoWrapperFoundException;
use suda\framework\response\wrapper\FileContentWrapper;
use suda\framework\response\wrapper\HtmlContentWrapper;
use suda\framework\response\wrapper\JsonContentWrapper;
use suda\framework\response\wrapper\NullContentWrapper;

/**
 * 内容包装
 */
class ContentWrapper
{
    protected $types = [
       [ JsonContentWrapper::class , ['array', JsonSerializable::class],],
       [ HtmlContentWrapper::class , ['boolean', 'integer','double', 'string'],],
       [ NullContentWrapper::class , ['NULL'],],
       [ FileContentWrapper::class , [SplFileObject::class],],
    ];

    /**
     * 注册包装器
     *
     * @param string $provider
     * @param array $types
     * @return void
     */
    public function register(string $provider, array $types)
    {
        array_unshift($this->types, [$provider, $types]);
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

    /**
     * 包装
     *
     * @param mixed $content
     * @return AbstractContentWrapper
     */
    public function getWrapper($content): AbstractContentWrapper
    {
        foreach ($this->types as $typeConfig) {
            list($wrapper, $types) = $typeConfig;
            foreach ($types as $type) {
                if (static::isTypeOf($content, $type)) {
                    return new $wrapper($content, $type);
                }
            }
        }
        if (method_exists($content, '__toString')) {
            return new HtmlContentWrapper($content, 'string');
        }
        throw new NoWrapperFoundException(sprintf('no wrapper for type %s', get_class($content)), E_USER_ERROR);
    }
}
