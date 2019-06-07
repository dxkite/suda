<?php
namespace suda\database\middleware;

use ReflectionException;
use suda\database\struct\TableStruct;

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
