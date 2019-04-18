<?php
namespace suda\orm\middleware;

use suda\orm\TableStruct;
use suda\orm\middleware\Middleware;
use suda\orm\middleware\ObjectMiddleware;

/**
 * 感知表结构
 */
trait ObjectMiddlewareTrait
{
    public static function createMiddleware(TableStruct $struct):Middleware
    {
        return new ObjectMiddleware(static::class);
    }
}
