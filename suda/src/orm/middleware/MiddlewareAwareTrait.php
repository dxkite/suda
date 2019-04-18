<?php
namespace suda\orm\middleware;

use suda\orm\TableStruct;
use suda\orm\middleware\Middleware;
use suda\orm\struct\TableStructMiddleware;

/**
 * 感知表结构
 */
trait MiddlewareAwareTrait
{
    /**
     * 表结构
     *
     * @var Middleware
     */
    protected static $middleware;

    public static function getMiddleware(TableStruct $struct):Middleware
    {
        if (static::$middleware === null) {
            static::$middleware = static::createMiddleware($struct);
        }
        return static::$middleware;
    }

    abstract public static function createMiddleware(TableStruct $struct):Middleware;
}
