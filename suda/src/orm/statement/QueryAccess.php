<?php
namespace suda\orm\statement;

use function microtime;
use PDO;
use PDOStatement;
use function preg_replace;
use suda\orm\Binder;
use suda\orm\statement\Statement;
use suda\orm\connection\Connection;
use suda\orm\middleware\Middleware;
use suda\orm\statement\QueryResult;
use suda\orm\exception\SQLException;
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
     * @param Connection $connection
     * @param Middleware $middleware
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
     * @return string 则获取失败，整数则获取成功
     */
    public function lastInsertId(string $name = null):string
    {
        return $this->connection->lastInsertId($name);
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
        return $this->createResult($statement);
    }


    /**
     * 获取运行结果
     *
     * @param Statement $statement
     * @return mixed
     */
    protected function createResult(Statement $statement)
    {
        return (new QueryResult($this->connection, $this->middleware))->createResult($statement);
    }

    /**
     * 设置中间件
     *
     * @param Middleware $middleware
     * @return $this
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
     * @throws SQLException
     */
    protected function createPDOStatement(Connection $source, Statement $statement): PDOStatement
    {
        $statement->prepare();
        $queryObj = $statement->getQuery();
        $query = $this->prefix($queryObj->getQuery());
        if ($statement->isScroll()) {
            $stmt = $source->getPdo()->prepare($query, [PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL]);
        } else {
            $stmt = $source->getPdo()->prepare($query);
        }
        if ($stmt instanceof PDOStatement) {
            return $stmt;
        }
        throw new SQLException(sprintf('error prepare %s', $statement->getString()), SQLException::ERR_PREPARE);
    }

    /**
     * 绑定值
     *
     * @param PDOStatement $stmt
     * @param Statement $statement
     * @return void
     */
    protected function bindPDOStatementValues(PDOStatement $stmt, Statement $statement)
    {
        foreach ($statement->getQuery()->getBinder() as $binder) {
            if ($binder->getKey() !== null) {
                $value = $this->middleware->input($binder->getKey(), $binder->getValue());
                $stmt->bindValue($binder->getName(), $value, Binder::typeOf($value));
            } else {
                $stmt->bindValue($binder->getName(), $binder->getValue(), Binder::typeOf($binder->getValue()));
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
        if ($statement->isScroll() && $statement->getStatement() !== null) {
            // noop
        } else {
            $stmt = $this->createPDOStatement($connection, $statement);
            $this->bindPDOStatementValues($stmt, $statement);
            $statement->setStatement($stmt);
            $start = microtime(true);
            $status = $stmt->execute();
            $connection->getObserver()->observe($this, $statement, microtime(true) - $start, $status);
            if ($status === false) {
                throw new SQLException(implode(':', $stmt->errorInfo()), intval($stmt->errorCode()));
            }
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
     * 自动填充前缀
     *
     * @param string $query
     * @return string
     */
    public function prefix(string $query):string
    {
        if ($prefix = $this->connection->getConfig('prefix')) {
            // _:table 前缀控制
            return preg_replace('/_:(\w+)/', $prefix.'$1', $query);
        }
        return $query;
    }
}
