<?php
namespace suda\orm\middleware;

use suda\orm\TableStruct;
use suda\orm\middleware\Middleware;

/**
 * 感知表结构
 */
interface MiddlewareAwareInterface
{
    public static function createMiddleware(TableStruct $struc):Middleware;
    public static function getMiddleware(TableStruct $struct):Middleware;
}
