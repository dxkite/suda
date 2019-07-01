<?php

namespace suda\database\statement;

use PDOStatement;
use suda\database\Binder;
use suda\database\exception\SQLException;
use suda\database\middleware\Middleware;

abstract class Statement extends StatementConfig
{
    use PrepareTrait;

    /**
     * 绑定
     *
     * @var Binder[]
     */
    protected $binder = [];

    /**
     * SQL语句
     *
     * @var string
     */
    protected $string;

    /**
     * PDOStatement
     *
     * @var PDOStatement|null
     */
    protected $statement = null;

    /**
     * Query
     *
     * @var Query
     */
    protected $query;

    /**
     * 类
     *
     * @var string
     */
    protected $fetchClass;

    /**
     * 参数
     *
     * @var array
     */
    protected $fetchClassArgs = [];

    /**
     * 数据处理中间件
     *
     * @var Middleware
     */
    protected $middleware;

    /**
     * 运行结果
     *
     * @var bool
     */
    protected $success;

    /**
     * Statement constructor.
     * @param string $sql
     * @param mixed ...$args
     * @throws SQLException
     */
    public function __construct(string $sql, ...$args)
    {
        if (count($args) === 1 && is_array($args[0])) {
            $this->create($sql, $args[0]);
        } else {
            list($this->string, $this->binder) = $this->prepareQueryMark($sql, $args);
        }
    }

    /**
     * @param string $sql
     * @param array $parameter
     * @throws SQLException
     */
    protected function create(string $sql, array $parameter)
    {
        list($this->string, $binder) = $this->prepareWhereString($sql, $parameter);
        $this->binder = $this->mergeBinder($this->binder, $binder);
    }

    /**
     * 设置记录类
     *
     * @param string|null $class
     * @param array $args
     * @return $this
     */
    public function setFetchType(?string $class = null, array $args = [])
    {
        $this->fetchClass = $class;
        $this->fetchClassArgs = $args;
        return $this;
    }

    /**
     * 获取取值类
     *
     * @return string|null
     */
    public function getFetchClass(): ?string
    {
        return $this->fetchClass ?? null;
    }


    /**
     * 获取SQL字符串
     *
     * @return string
     */
    public function getString()
    {
        return $this->getQuery()->getQuery();
    }

    /**
     * 准备查询对象
     *
     * @return Query
     */
    protected function prepareQuery(): Query
    {
        return new Query($this->string, $this->binder);
    }

    /**
     * 准备查询对象
     *
     * @return Query
     */
    public function prepare(): Query
    {
        return $this->query = $this->prepareQuery();
    }

    /**
     * 获取查询对象
     *
     * @return Query
     */
    public function getQuery(): Query
    {
        if ($this->query === null) {
            $this->query = $this->prepare();
        }
        return $this->query;
    }

    /**
     * @param Query $query
     */
    public function setQuery(Query $query): void
    {
        $this->query = $query;
        $this->string = $query->getQuery();
        $this->binder = $query->getBinder();
    }

    /**
     * 获取绑定信息
     *
     * @return Binder[]
     */
    public function getBinder()
    {
        return $this->binder;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getString();
    }


    /**
     * Get PDOStatement
     *
     * @return  PDOStatement
     */
    public function getStatement(): ?PDOStatement
    {
        return $this->statement;
    }

    /**
     * Set PDOStatement
     *
     * @param PDOStatement $statement PDOStatement
     *
     * @return  $this
     */
    public function setStatement(PDOStatement $statement)
    {
        $this->statement = $statement;

        return $this;
    }

    /**
     * Get 参数
     *
     * @return  array
     */
    public function getFetchClassArgs()
    {
        return $this->fetchClassArgs;
    }

    /**
     * Get 数据处理中间件
     * @return  Middleware
     */
    public function getMiddleware()
    {
        return $this->middleware;
    }

    /**
     * Set 数据处理中间件
     *
     * @param Middleware $middleware 数据处理中间件
     * @return  $this
     */
    public function setMiddleware(Middleware $middleware)
    {
        $this->middleware = $middleware;
        return $this;
    }

    /**
     * @param Binder $binder
     * @return $this
     */
    public function addBinder(Binder $binder)
    {
        if (!in_array($binder, $this->binder)) {
            $this->binder[] = $binder;
        }
        return $this;
    }

    /**
     * 添加参数
     *
     * @param string $name
     * @param mixed $value
     * @param string|null $key
     * @return $this
     */
    public function addValue(string $name, $value, ?string $key = null)
    {
        return $this->addBinder(new Binder($name, $value, $key));
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * @param bool $success
     */
    public function setSuccess(bool $success): void
    {
        $this->success = $success;
    }
}
