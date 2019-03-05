<?php
namespace suda\orm;

use PDO;
use PDOStatement;

use suda\orm\DataSource;
use suda\orm\Middleware;
use suda\orm\TableStruct;
use suda\archive\creator\Binder;
use suda\orm\statement\Statement;
use suda\orm\statement\WriteStatement;

class TableAccess
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
     * 中间件
     *
     * @var Middleware|null
     */
    protected $middleware;

    /**
     * 创建数据表
     *
     * @param DataSource $source
     * @param TableStruct $struct
     * @param Middleware $middleware
     */
    public function __construct(TableStruct $struct, DataSource $source, Middleware $middleware = null)
    {
        $this->source = $source;
        $this->struct = $struct;
        $this->middleware = $middleware;
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
        $source->switchTable($this->struct->getName());

        $result = $this->runStatement($statement);

        if ($result === false && $statement->isFetch()) {
            throw new SQLException('run '.$statement.' error, could not fetch');
        }

        if ($result && $statement->isFetch()) {
            return $this->fetchResult($statement);
        }
        return $result;
    }

    /**
     * 设置中间件
     *
     * @param Middleware $middleware
     * @return self
     */
    public function middleware(Middleware $middleware)
    {
        $this->middleware = $middleware;
        return $this;
    }

    /**
     * 写
     *
     * @param mixed ...$args
     * @return WriteStatement
     */
    public function write(...$args):WriteStatement {
        return (new WriteStatement($this->source->write()->rawTableName($this->struct->getName()), $this->struct))->write(...$args);
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
     * 创建SQL语句
     *
     * @return PDOStatement
     */
    protected function createStmt(Statement $statement): PDOStatement
    {
        if ($statement->scroll()) {
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
            if ($binder->getKey() !== null && $this->middleware !== null) {
                $value = $this->middleware->input($binder->getKey(), $binder->getValue());
                $stmt->bindValue($binder->getName(), $value, Binder::bindParam($value));
            } else {
                $stmt->bindValue($binder->getName(), $binder->getValue(), Binder::bindParam($value));
            }
        }
    }

    /**
     * 处理一行数据
     *
     * @param array $data
     * @return array
     */
    protected function fetchOneProccess(array $data):array
    {
        foreach ($data as $name => $value) {
            $data[$name] = $this->middleware->output($name, $value);
        }
        return $this->struct->createOne($data);
    }

    /**
     * 处理多行数据
     *
     * @param array $data
     * @return array
     */
    protected function fetchAllProccess(array $data):array
    {
        foreach ($data as $index => $row) {
            $row = $this->fetchOneProccess($row);
            $data[$index] = $this->middleware->outputRow($row);
        }
        return $data;
    }

    /**
     * 运行语句
     *
     * @param Statement $statement
     * @return boolean
     */
    protected function runStatement(Statement $statement):bool
    {
        if ($statement->scroll() && $this->getStatement() !== null) {
            $stmt = $this->getStatement();
            return true;
        } else {
            $stmt = $this->createStmt($statement);
            $this->bindStmt($stmt, $statement);
            $statement->setStatement($stmt);
            return $stmt->execute();
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
            return $this->fetchOneProccess($statement, $statement->getStatement()->fetch(PDO::FETCH_ASSOC));
        } elseif ($statement->isFetchAll()) {
            return $this->fetchAllProccess($statement, $statement->getStatement()->fetchAll(PDO::FETCH_ASSOC));
        }
    }
}
