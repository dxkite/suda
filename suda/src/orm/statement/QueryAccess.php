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

class QueryAccess
{
    /**
     * 数据源
     *
     * @var Connection
     */
    protected $connection;

    /**
     * 中间件
     *
     * @var Middleware
     */
    protected $middleware;

    /**
     * 创建运行器
     *
     * @param \suda\orm\connection\Connection $connection
     * @param \suda\orm\middleware\Middleware $middleware
     */
    public function __construct(Connection $connection, Middleware $middleware = null)
    {
        $this->connection = $connection;
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
        return $this->connection->lastInsertId();
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
        $this->connection->beginTransaction();
    }

    /**
     * 事务系列，提交事务
     *
     * @return void
     */
    public function commit()
    {
        $this->connection->commit();
    }

    /**
     * 事务系列，撤销事务
     *
     * @return void
     */
    public function rollBack()
    {
        $this->connection->rollBack();
    }


    /**
     * 运行SQL语句
     *
     * @param Statement $statement
     * @return mixed
     */
    public function run(Statement $statement)
    {
        $this->runStatement($this->connection, $statement);
        return $this->resultFrom($statement);
    }

    /**
     * 获取运行结果
     *
     * @param Statement $statement
     * @return mixed
     */
    protected function resultFrom(Statement $statement)
    {
        if ($statement->isWrite()) {
            return $statement->getStatement()->rowCount() > 0;
        } elseif ($statement->isFetch()) {
            return $this->fetchResult($statement);
        }
        return null;
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
            $stmt =  $source->getPdo()->prepare($statement->getString(), [PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL]);
        } else {
            $stmt =  $source->getPdo()->prepare($statement->getString());
        }
        if ($stmt !== false) {
            return $stmt;
        }
        throw new SQLException(sprintf("error prepare %s", $statement->getString()), SQLException::ERROR_PREPARE);
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
    protected function runStatement(Connection $connection, Statement $statement)
    {
        if ($statement->scroll() && $statement->getStatement() !== null) {
            $stmt = $this->getStatement();
        } else {
            $stmt = $this->createStmt($connection, $statement);
            $this->bindStmt($stmt, $statement);
            $statement->setStatement($stmt);
            $start = \microtime(true);
            $status = $stmt->execute();
            $connection->getObserver()->observe($statement, \microtime(true) - $start, $status);
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
}
