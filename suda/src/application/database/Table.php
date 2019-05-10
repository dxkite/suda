<?php
namespace suda\application\database;

use suda\orm\TableAccess;
use suda\orm\struct\TableStruct;
use suda\application\Application;
use suda\orm\middleware\Middleware;

/**
 * 数据表抽象对象
 *
 * 用于提供对数据表的操作
 *
 */
abstract class Table extends TableAccess implements Middleware
{
    use TableMiddlewareTrait;

    /**
     * 应用引用
     *
     * @var Application
     */
    protected static $application;

    /**
     * 从应用创建表
     *
     * @param Application $application
     * @return void
     */
    public static function load(Application $application)
    {
        static::$application = $application;
    }

    public function __construct(string $tableName)
    {
        parent::__construct($this->initStruct($tableName), static::$application->getDataSource(), $this);
    }

    abstract public function onCreateStruct(TableStruct $table):TableStruct;

    /**
     * 创建表结构
     *
     * @param string $tableName
     * @return TableStruct
     */
    protected function initStruct(string $tableName):TableStruct
    {
        $table = new TableStruct($tableName);
        return $this->onCreateStruct($table);
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
