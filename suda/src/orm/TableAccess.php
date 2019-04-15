<?php
namespace suda\orm;

use PDO;
use PDOStatement;
use suda\orm\Binder;
use suda\orm\DataSource;
use suda\orm\TableStruct;
use suda\orm\statement\Statement;
use suda\orm\struct\ReadStatement;
use suda\orm\connection\Connection;
use suda\orm\middleware\Middleware;
use suda\orm\statement\QueryAccess;
use suda\orm\struct\QueryStatement;
use suda\orm\struct\WriteStatement;
use suda\orm\exception\SQLException;
use suda\orm\middleware\NullMiddleware;

/**
 * 提供了对数据表的操作
 */
class TableAccess extends QueryAccess
{

    /**
     * 数据源
     *
     * @var DataSource
     */
    protected $source;

    /**
     * 表结构
     *
     * @var TableStruct
     */
    protected $struct;

    /**
     * 创建数据表
     *
     * @param DataSource $source
     * @param TableStruct $struct
     * @param Middleware $middleware
     */
    public function __construct(TableStruct $struct, DataSource $source, ?Middleware $middleware = null)
    {
        parent::__construct($source->write(), $middleware);
        $this->source = $source;
        $this->struct = $struct;
    }

    /**
     * 设置中间件
     *
     * @param Middleware $middleware
     * @return self
     */
    public function middleware(Middleware $middleware)
    {
        return $this->setMiddleware($middleware);
    }

    /**
     * 获取表结构
     *
     * @return \suda\orm\TableStruct
     */
    public function getStruct():TableStruct
    {
        return $this->struct;
    }

    /**
     * 获取表名
     *
     * @return string
     */
    public function getName():string
    {
        return $this->struct->getName();
    }

    /**
     * 获取最后一次插入的主键ID（用于自增值
     *
     * @param string $name
     * @return string 则获取失败，整数则获取成功
     */
    public function lastInsertId(string $name = null):string
    {
        return $this->source->write()->lastInsertId($name);
    }

    /**
     * 事务系列，开启事务
     *
     * @return void
     */
    public function beginTransaction()
    {
        $this->source->write()->beginTransaction();
    }

    /**
     * 事务系列，提交事务
     *
     * @return void
     */
    public function commit()
    {
        $this->source->write()->commit();
    }

    /**
     * 事务系列，撤销事务
     *
     * @return void
     */
    public function rollBack()
    {
        $this->source->write()->rollBack();
    }

    /**
     * 写
     *
     * @param string|array $name
     * @param mixed $value
     * @return \suda\orm\struct\WriteStatement
     */
    public function write($name, $value = null):WriteStatement
    {
        return (new WriteStatement($this))->write($name, $value);
    }


    /**
     * 删
     *
     * @param string|array $where
     * @param array $whereParameter
     * @return \suda\orm\struct\WriteStatement
     */
    public function delete($where = null, ...$whereParameter):WriteStatement
    {
        if ($where !== null) {
            return (new WriteStatement($this))->delete()->where($where, ...$whereParameter);
        }
        return (new WriteStatement($this))->delete();
    }

    /**
     * 读
     *
     * @param mixed ...$args
     * @return ReadStatement
     */
    public function read(...$args):ReadStatement
    {
        return (new ReadStatement($this))->read(...$args);
    }

    /**
     * 原始查询
     *
     * @param string $query
     * @param mixed ...$parameter
     * @return \suda\orm\struct\QueryStatement
     */
    public function query(string $query, ...$parameter):QueryStatement
    {
        return (new QueryStatement($this, $query, ...$parameter));
    }

    /**
     * 运行SQL语句
     *
     * @param Statement $statement
     * @return mixed
     */
    public function run(Statement $statement)
    {
        $connection = $statement->isRead() ? $this->source->read() : $this->source->write();
        $this->runStatement($connection, $statement);
        return $this->createResult($statement);
    }

    /**
     * 获取数据源
     *
     * @return  DataSource
     */
    public function getSource():DataSource
    {
        return $this->source;
    }


    /**
     * 设置数据源
     *
     * @return  DataSource
     */
    public function setSource(DataSource $source)
    {
        return $this->source = $source;
    }
}
