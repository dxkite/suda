<?php
namespace suda\database;

use function array_keys;
use function func_get_args;
use function func_num_args;
use suda\database\struct\ReadStatement;
use suda\database\middleware\Middleware;
use suda\database\statement\QueryAccess;
use suda\database\struct\QueryStatement;
use suda\database\struct\TableStruct;
use suda\database\struct\WriteStatement;

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
        parent::__construct($source, $middleware);
        $this->struct = $struct;
    }

    /**
     * 设置中间件
     *F
     * @param Middleware $middleware
     * @return $this
     */
    public function middleware(Middleware $middleware)
    {
        return $this->setMiddleware($middleware);
    }

    /**
     * 获取表结构
     *
     * @return TableStruct
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
     * 写
     *
     * @param string|array $name
     * @param mixed $value
     * @return WriteStatement
     * @throws exception\SQLException
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
     * @return WriteStatement
     * @throws exception\SQLException
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
     * @param mixed $fields
     * @return ReadStatement
     */
    public function read($fields):ReadStatement
    {
        if ($fields === null) {
            $fields = array_keys($this->getStruct()->all());
        } elseif (func_num_args() > 1) {
            $fields = func_get_args();
        }
        return (new ReadStatement($this))->read($fields);
    }

    /**
     * 原始查询
     *
     * @param string $query
     * @param mixed ...$parameter
     * @return QueryStatement
     * @throws exception\SQLException
     */
    public function query(string $query, ...$parameter):QueryStatement
    {
        return (new QueryStatement($this, $query, ...$parameter));
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
     * @param DataSource $source
     * @return  DataSource
     */
    public function setSource(DataSource $source)
    {
        return $this->source = $source;
    }
}
