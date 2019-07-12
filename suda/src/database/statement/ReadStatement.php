<?php

namespace suda\database\statement;

use function func_get_args;
use function func_num_args;
use function is_array;
use suda\database\struct\TableStruct;
use suda\database\middleware\Middleware;
use suda\database\exception\SQLException;

/**
 * Class ReadStatement
 * @package suda\database\statement
 */
class ReadStatement extends QueryStatement
{
    use PrepareTrait;

    /**
     * 数据表结构
     *
     * @var TableStruct
     */
    protected $struct;

    /**
     * 数据原始表名
     *
     * @var string
     */
    protected $table;

    /**
     * @var string
     */
    protected $distinct = '';

    /**
     * @var string
     */
    protected $select = '*';

    /**
     * @var string
     */
    protected $where = '';

    /**
     * @var string
     */
    protected $groupBy = '';

    /**
     * @var string
     */
    protected $having = '';

    /**
     * @var string
     */
    protected $orderBy = '';

    /**
     * @var string
     */
    protected $limit = '';

    /**
     * 创建写
     *
     * @param string $rawTableName
     * @param TableStruct $struct
     * @param Middleware $middleware
     * @throws SQLException
     */
    public function __construct(string $rawTableName, TableStruct $struct, Middleware $middleware)
    {
        parent::__construct('');
        $this->struct = $struct;
        $this->table = $rawTableName;
        $this->type = self::READ;
        $this->fetch = self::FETCH_ONE;
        $this->middleware = $middleware;
    }

    /**
     * 单独去重复
     *
     * @return $this
     */
    public function distinct()
    {
        $this->distinct = 'DISTINCT';
        return $this;
    }

    /**
     * 查询的列
     *
     * @param array|string $fields
     * @return $this
     */
    public function read($fields)
    {
        if (func_num_args() > 1) {
            $fields = func_get_args();
        }
        if (is_array($fields)) {
            foreach ($fields as $index => $name) {
                $fields[$index] = $this->middleware->inputName($name);
            }
        }
        $this->select = $this->prepareReadFields($fields);
        return $this;
    }

    /**
     * 条件
     * @param $where
     * @param mixed ...$args
     * @return $this
     * @throws SQLException
     */
    public function where($where, ...$args)
    {
        if (is_array($where)) {
            $where = $this->aliasKeyField($where);
            $this->whereArray($where, $args[0] ?? []);
        } elseif (count($args) > 0 && is_array($args[0])) {
            $this->whereStringArray($where, $args[0]);
        } else {
            list($string, $array) = $this->prepareQueryMark($where, $args);
            $this->whereStringArray($string, $array);
        }
        return $this;
    }

    /**
     * 处理输入的键
     *
     * @param array $fields
     * @return array
     */
    protected function aliasKeyField(array $fields)
    {
        $values = [];
        foreach ($fields as $name => $value) {
            $index = $this->middleware->inputName($name);
            $values[$index] = $value;
        }
        return $values;
    }

    /**
     * @param array $where
     * @param array $binders
     * @throws SQLException
     */
    protected function whereArray(array $where, array $binders)
    {
        list($where, $whereBinder) = $this->prepareWhere($where);
        $this->whereStringArray($where, array_merge($whereBinder, $binders));
    }

    /**
     * @param string $where
     * @param array $whereBinder
     * @throws SQLException
     */
    protected function whereStringArray(string $where, array $whereBinder)
    {
        list($where, $whereBinder) = $this->prepareWhereString($where, $whereBinder);
        $this->where = 'WHERE ' . $where;
        $this->binder = $this->mergeBinder($this->binder, $whereBinder);
    }

    /**
     * 分组
     *
     * @param string $what
     * @return $this
     */
    public function groupBy(string $what)
    {
        $this->groupBy = 'GROUP BY `' . $what . '`';
        return $this;
    }

    /**
     * 含
     *
     * @param string|array $what
     * @param array $args
     * @return $this
     * @throws SQLException
     */
    public function having($what, ...$args)
    {
        if (is_array($what)) {
            $this->havingArray($what);
        } elseif (is_array($args[0])) {
            $this->havingStringArray($what, $args[0]);
        } else {
            list($string, $array) = $this->prepareQueryMark($what, $args);
            $this->havingStringArray($string, $array);
        }
        return $this;
    }

    /**
     * @param array $want
     * @throws SQLException
     */
    protected function havingArray(array $want)
    {
        list($having, $havingBinder) = $this->prepareWhere($want);
        $this->havingStringArray($having, $havingBinder);
    }

    /**
     * @param string $having
     * @param array $havingBinder
     * @throws SQLException
     */
    protected function havingStringArray(string $having, array $havingBinder)
    {
        list($having, $havingBinder) = $this->prepareWhereString($having, $havingBinder);
        $this->having = 'HAVING ' . $having;
        $this->binder = $this->mergeBinder($this->binder, $havingBinder);
    }

    /**
     * 排序
     *
     * @param string $what
     * @param string $order
     * @return $this
     */
    public function orderBy(string $what, string $order = 'ASC')
    {
        $order = strtoupper($order);
        if (strlen($this->orderBy) > 0) {
            $this->orderBy .= ',`' . $what . '` ' . $order;
        } else {
            $this->orderBy = 'ORDER BY `' . $what . '` ' . $order;
        }
        return $this;
    }

    /**
     * 限制
     *
     * @param int $start
     * @param integer $length
     * @return $this
     */
    public function limit(int $start, int $length = null)
    {
        $this->limit = 'LIMIT ' . $start . ($length !== null ? ',' . $length : '');
        return $this;
    }

    /**
     * 分页
     *
     * @param integer $page
     * @param integer $length
     * @return $this
     */
    public function page(int $page, int $length)
    {
        if ($page <= 0) {
            $page = 1;
        }
        $this->limit(($page - 1) * $length, $length);
        return $this;
    }

    /**
     * 获取字符串
     *
     * @return Query
     */
    protected function prepareQuery(): Query
    {
        $where = [$this->where, $this->groupBy, $this->having, $this->orderBy, $this->limit];
        $condition = implode(' ', array_filter(array_map('trim', $where), 'strlen'));
        $select = [$this->distinct, $this->select];
        $selection = implode(' ', array_filter(array_map('trim', $select), 'strlen'));
        $string = sprintf("SELECT %s FROM %s %s", $selection, $this->table, $condition);
        return new Query($string, $this->binder);
    }
}
