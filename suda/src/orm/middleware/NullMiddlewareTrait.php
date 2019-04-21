<?php
namespace suda\orm\middleware;

use suda\orm\TableStruct;
use suda\orm\middleware\Middleware;

/**
 * 感知表结构
 */
trait NullMiddlewareTrait
{
    public static function getMiddleware(TableStruct $struct):Middleware
    {
        return new NullMiddleware;
    }
}
