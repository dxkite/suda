<?php
/**
 * Suda FrameWork
 *
 * An open source application development framework for PHP 7.2.0 or newer
 *
 * Copyright (c)  2017-2018 DXkite
 *
 * @category   PHP FrameWork
 * @package    Suda
 * @copyright  Copyright (c) DXkite
 * @license    MIT
 * @link       https://github.com/DXkite/suda
 * @version    since 1.2.4
 */
namespace suda\core;

use suda\archive\SQLQuery;
use suda\archive\RawQuery;
use suda\exception\SQLException;

/**
 * 数据库查询类
 * 提供了数据库的查询的静态封装
 */
class Query
{
    public static function __callStatic(string $method, $args)
    {
        $method = new \ReflectionMethod(SQLQuery::class, $method);
        if ($method->isStatic()) {
            return $method->invokeArgs(null, $args);
        } else {
            return $method->invokeArgs(new SQLQuery, $args);
        }
    }
}
