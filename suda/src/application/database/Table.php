<?php
namespace suda\application\database;

use suda\orm\DataSource;
use suda\orm\TableAccess;
use suda\orm\TableStruct;
use suda\orm\middleware\Middleware;
use suda\application\database\TableMiddlewareTrait;

/**
 * 数据表抽象对象
 *
 * 用于提供对数据表的操作
 *
 */
abstract class Table extends TableAccess implements Middleware
{
    use TableMiddlewareTrait;

    public function __construct(string $tableName, DataSource $dataSource)
    {
        parent::__construct($this->initStruct($tableName), $dataSource, $this);
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
}
