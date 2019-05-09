<?php
namespace suda\orm\middleware;

use suda\orm\TableStruct;

/**
 * 感知表结构
 */
trait NullMiddlewareTrait
{
    /**
     * @param TableStruct $struct
     * @return Middleware
     */
    public static function getMiddleware(TableStruct $struct):Middleware
    {
        return new NullMiddleware;
    }
}
