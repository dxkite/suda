<?php
namespace suda\orm\middleware;

use ReflectionException;
use suda\orm\TableStruct;

/**
 * 感知表结构
 */
trait ObjectMiddlewareTrait
{
    /**
     * @param TableStruct $struct
     * @return Middleware
     * @throws ReflectionException
     */
    public static function createMiddleware(TableStruct $struct):Middleware
    {
        return new ObjectMiddleware(static::class);
    }
}
