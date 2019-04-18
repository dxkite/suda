<?php
namespace suda\application\database;

use suda\orm\DataSource;
use suda\application\Application;
use suda\orm\middleware\Middleware;

/**
 * 数据表抽象对象
 *
 * 用于提供对数据表的操作
 *
 */
class DataAccess extends \suda\orm\DataAccess
{
    

    /**
     * 应用引用
     *
     * @var Application
     */
    protected static $application;


    /**
     * 创建对数据的操作
     *
     * @param string $object
     * @param Middleware|null $middleware
     */
    public function __construct(string $object, ?Middleware $middleware = null)
    {
        parent::__construct($object, static::$application->getDataSource(), $middleware);
    }

    /**
     * 从变量创建中间件
     *
     * @param object $object
     * @return DataAccess
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
     * @param \suda\orm\middleware\Middleware|null $middleware
     * @return DataAccess
     */
    public static function new(string $object, ?Middleware $middleware = null):DataAccess
    {
        return new self($object, $middleware);
    }

    /**
     * 从应用创建表
     *
     * @param \suda\application\Application $application
     * @return void
     */
    public static function load(Application $application)
    {
        static::$application = $application;
    }

    /**
     * Get 应用引用
     *
     * @return  Application
     */
    public static function application()
    {
        return static::$application;
    }
}
