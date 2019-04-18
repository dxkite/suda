<?php
namespace suda\orm\struct;

use suda\orm\TableStruct;
use suda\orm\middleware\Middleware;
use suda\orm\struct\TableStructMiddleware;

/**
 * 感知表结构
 */
trait TableStructMiddlewareTrait
{
    public static function createMiddleware(TableStruct $struct):Middleware
    {
        return new TableStructMiddleware(static::class, $struct);
    }
}
