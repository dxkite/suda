<?php
namespace suda\orm\statement;

use PDO;
use PDOStatement;
use suda\orm\Binder;
use suda\orm\DataSource;
use suda\orm\TableStruct;
use suda\orm\statement\Statement;
use suda\orm\connection\Connection;
use suda\orm\middleware\Middleware;
use suda\orm\exception\SQLException;
use suda\orm\statement\ReadStatement;
use suda\orm\middleware\NullMiddleware;

class StatementRunner
{
    /**
     * 数据源
     *
     * @var DataSource
     */
    protected $source;

    /**
     * 中间件
     *
     * @var Middleware
     */
    protected $middleware;


    /**
     * 创建语句运行器
     *
     * @param \suda\orm\DataSource $source
     * @param \suda\orm\middleware\Middleware $middleware
     */
    public function __construct(DataSource $source, Middleware $middleware = null)
    {
        $this->source = $source;
        $this->middleware = $middleware ?: new NullMiddleware;
    }
    /**
     * 获取最后一次插入的主键ID（用于自增值
     *
     * @param string $name
     * @return null|int 则获取失败，整数则获取成功
     */
    public function lastInsertId(string $name = null):?int
    {
        return $this->source->write()->lastInsertId();
    }

    /**
     * 事务系列，开启事务
     *
     * @return void
     */
    public function begin()
    {
        $this->beginTransaction();
    }

    /**
     * 事务系列，开启事务
     *
     * @return void
     */
    public function beginTransaction()
    {
        return $this->source->write()->beginTransaction();
    }

    /**
     * 事务系列，提交事务
     *
     * @return void
     */
    public function commit()
    {
        return $this->source->write()->commit();
    }

    /**
     * 事务系列，撤销事务
     *
     * @return void
     */
    public function rollBack()
    {
        return $this->source->write()->rollBack();
    }


    /**
     * 运行SQL语句
     *
     * @param Statement $statement
     * @return mixed
     */
    public function run(Statement $statement)
    {
        $source = $statement->isRead() ? $this->source->read() : $this->source->write();
        $this->runStatement($source, $statement);
        if ($statement->isWrite()) {
            return $statement->getStatement()->rowCount() > 0;
        } elseif ($statement->isFetch()) {
            return $this->fetchResult($statement);
        }
        return null;
    }

    /**
     * 直接查询
     *
     * @param string $sql
     * @param array $parameter
     * @return mixed
     */
    public function query(string $sql, array $parameter = [])
    {
        $statement = new Statement($sql, $parameter);
        return $this->run($statement);
    }

    /**
     * 设置中间件
     *
     * @param Middleware $middleware
     * @return self
     */
    public function setMiddleware(Middleware $middleware)
    {
        $this->middleware = $middleware;
        return $this;
    }

    /**
     * 创建SQL语句
     *
     * @param Connection $source
     * @param Statement $statement
     * @return PDOStatement
     */
    protected function createStmt(Connection $source, Statement $statement): PDOStatement
    {
        if ($statement->scroll() === true) {
            return $source->getPdo()->prepare($statement->getString(), [PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL]);
        } else {
            return $source->getPdo()->prepare($statement->getString());
        }
    }

    /**
     * 绑定值
     *
     * @param PDOStatement $stmt
     * @param Statement $statement
     * @return void
     */
    protected function bindStmt(PDOStatement $stmt, Statement $statement)
    {
        foreach ($statement->getBinder() as $binder) {
            if ($binder->getKey() !== null) {
                $value = $this->middleware->input($binder->getKey(), $binder->getValue());
                $stmt->bindValue($binder->getName(), $value, Binder::build($value));
            } else {
                $stmt->bindValue($binder->getName(), $binder->getValue(), Binder::build($binder->getValue()));
            }
        }
    }

    /**
     * 运行语句
     *
     * @param Statement $statement
     * @return void
     */
    protected function runStatement(Connection $source, Statement $statement)
    {
        if ($statement->scroll() && $this->getStatement() !== null) {
            $stmt = $this->getStatement();
        } else {
            $stmt = $this->createStmt($source, $statement);
            $this->bindStmt($stmt, $statement);
            $statement->setStatement($stmt);
            $start = \microtime(true);
            $status = $stmt->execute();
            $source->getObserver()->observe($statement, \microtime(true) - $start, $status);
            if ($status === false) {
                throw new SQLException($stmt->errorInfo()[2], intval($stmt->errorCode()));
            }
        }
    }

    /**
     * 取结果
     *
     * @param Statement $statement
     * @return mixed
     */
    protected function fetchResult(Statement $statement)
    {
        if ($statement->isFetchOne()) {
            return $statement->getStatement()->fetch(PDO::FETCH_ASSOC);
        } elseif ($statement->isFetchAll()) {
            return $statement->getStatement()->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    /**
     * Get 中间件
     *
     * @return  Middleware
     */
    public function getMiddleware():Middleware
    {
        return $this->middleware;
    }

    /**
     * Get 中间件
     *
     * @return  DataSource
     */
    public function getSource():DataSource
    {
        return $this->source;
    }
}
