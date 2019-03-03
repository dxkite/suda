<?php
namespace suda\framework\response;

use SplFileInfo;
use ReflectionClass;
use JsonSerializable;
use suda\framework\Response;
use suda\framework\response\AbstractContentWrapper;
use suda\framework\response\wrapper\FileContentWrapper;
use suda\framework\response\wrapper\HtmlContentWrapper;
use suda\framework\response\wrapper\JsonContentWrapper;
use suda\framework\response\wrapper\NullContentWrapper;

/**
 * 内容包装
 */
class ContentWrapper
{
    protected static $types = [
        JsonContentWrapper::class => ['array', JsonSerializable::class],
        HtmlContentWrapper::class => ['boolean', 'integer','double', 'string'],
        NullContentWrapper::class => ['NULL'],
        FileContentWrapper::class => [SplFileInfo::class],
    ];

    /**
     * 注册包装器
     *
     * @param string $provider
     * @param array $types
     * @return void
     */
    public static function register(string $provider, array $types)
    {
        static::$types[$provider] = $types;
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
        if (is_object($data) && !\in_array($type, ['boolean', 'integer','double', 'string','array','NULL'])) {
            $class = new ReflectionClass($data);
            $typeRef = new ReflectionClass($type);
            if ($typeRef->isInterface()) {
                return $class->implementsInterface($type);
            } else {
                return $class->isSubclassOf($type) || $typeRef->isInstance($class);
            }
        } else {
            return \gettype($data) === $type;
        }
    }

    /**
     * 包装
     *
     * @param mixed $content
     * @return AbstractContentWrapper
     */
    public static function getWrapper($content): AbstractContentWrapper
    {
        foreach (static::$types as $wrapper => $types) {
            foreach ($types as $type) {
                if (static::isTypeOf($content, $type)) {
                    return new $wrapper($content, $type);
                }
            }
        }
        if (\method_exists($content, '__toString')) {
            return new HtmlContentWrapper($content, 'string');
        }
        throw new \Exception(sprintf('no wrapper for type %s', \get_class($content)));
    }
}
