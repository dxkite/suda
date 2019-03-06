<?php
namespace suda\orm;

use PDO;
use PDOStatement;

use suda\orm\Binder;
use suda\orm\DataSource;
use suda\orm\Middleware;

use suda\orm\TableStruct;
use suda\orm\statement\Statement;
use suda\orm\connection\Connection;
use suda\orm\exception\SQLException;
use suda\orm\statement\ReadStatement;
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
        $this->runStatement($source, $statement);
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
    public function write(...$args):WriteStatement
    {
        return (new WriteStatement($this->source->write()->rawTableName($this->struct->getName()), $this->struct))->write(...$args);
    }

    /**
     * 读
     *
     * @param mixed ...$args
     * @return ReadStatement
     */
    public function read(...$args):ReadStatement
    {
        return (new ReadStatement($this->source->write()->rawTableName($this->struct->getName()), $this->struct))->want(...$args);
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
            if ($binder->getKey() !== null && $this->middleware !== null) {
                $value = $this->middleware->input($binder->getKey(), $binder->getValue());
                $stmt->bindValue($binder->getName(), $value, Binder::build($value));
            } else {
                $stmt->bindValue($binder->getName(), $binder->getValue(), Binder::build($binder->getValue()));
            }
        }
    }

    /**
     * 处理一行数据
     *
     * @param array $data
     * @return TableStruct
     */
    protected function fetchOneProccess(array $data):TableStruct
    {
        if ($this->middleware !== null) {
            foreach ($data as $name => $value) {
                $data[$name] = $this->middleware->output($name, $value);
            }
        }
        return $this->struct->createOne($data);
    }

    /**
     * 处理多行数据
     *
     * @param array $data
     * @return TableStruct[]
     */
    protected function fetchAllProccess(array $data): array
    {
        foreach ($data as $index => $row) {
            $row = $this->fetchOneProccess($row);
            if ($this->middleware !== null) {
                $data[$index] = $this->middleware->outputRow($row);
            }
        }
        return $data;
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
            if ($stmt->execute() === false) {
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
            return $this->fetchOneProccess($statement->getStatement()->fetch(PDO::FETCH_ASSOC));
        } elseif ($statement->isFetchAll()) {
            return $this->fetchAllProccess($statement->getStatement()->fetchAll(PDO::FETCH_ASSOC));
        }
    }

    /**
     * Get 数据源
     *
     * @return  DataSource
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Get 表结构
     *
     * @return  TableStruct
     */
    public function getStruct()
    {
        return $this->struct;
    }

    /**
     * Get 中间件
     *
     * @return  Middleware|null
     */
    public function getMiddleware():?Middleware
    {
        return $this->middleware;
    }
}
