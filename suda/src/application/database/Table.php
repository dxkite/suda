<?php
namespace suda\application\database;

use suda\database\TableAccess;
use suda\database\struct\TableStruct;
use suda\database\middleware\Middleware;

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
     * Table constructor.
     * @param string $tableName
     */
    public function __construct(string $tableName)
    {
        parent::__construct($this->initStruct($tableName), Database::application()->getDataSource(), $this);
    }

    /**
     * 构建数据表
     * @param TableStruct $table
     * @return TableStruct
     */
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
     * 结构继承
     * @param Table $table
     * @return bool
     */
    public function isSubOf(Table $table)
    {
        return TableStruct::isSubOf($this->getStruct(), $table->getStruct());
    }
}
