<?php
namespace suda\application\database;

use ReflectionException;
use suda\database\middleware\Middleware;

/**
 * 数据表抽象对象
 *
 * 用于提供对数据表的操作
 *
 */
class DataAccess extends \suda\database\DataAccess
{


    /**
     * 创建对数据的操作
     *
     * @param string $object
     * @param Middleware|null $middleware
     * @throws ReflectionException
     */
    public function __construct(string $object, ?Middleware $middleware = null)
    {
        parent::__construct($object, Database::application()->getDataSource(), $middleware);
    }

    /**
     * 从变量创建中间件
     *
     * @param object $object
     * @return DataAccess
     * @throws ReflectionException
     */
    public static function create($object):DataAccess
    {
        $middleware = null;
        if ($object instanceof Middleware) {
            $middleware = $object;
        }
        return new self(get_class($object), $middleware);
    }

    /**
     * 创建访问工具
     *
     * @param string $object
     * @param Middleware|null $middleware
     * @return DataAccess
     * @throws ReflectionException
     */
    public static function new(string $object, ?Middleware $middleware = null):DataAccess
    {
        return new self($object, $middleware);
    }
}
