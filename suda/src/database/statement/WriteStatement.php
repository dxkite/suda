<?php

namespace suda\database\statement;

use function implode;
use function is_array;
use function is_string;
use function sprintf;
use suda\database\Binder;
use suda\database\struct\TableStruct;
use suda\database\middleware\Middleware;
use suda\database\exception\SQLException;

class WriteStatement extends Statement
{
    use PrepareTrait;

    /**
     * 数据
     *
     * @var array|string
     */
    protected $data;

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
     * 模板条件条件
     *
     * @var string|null
     */
    protected $whereCondition = null;

    /**
     * 是否为删除
     *
     * @var bool
     */
    protected $delete = false;


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
        $this->type = self::WRITE;
        $this->struct = $struct;
        $this->table = $rawTableName;
        $this->middleware = $middleware;
    }

    /**
     * 写数据
     *
     * @param string|array $name
     * @param mixed $value
     * @return $this
     * @throws SQLException
     */
    public function write($name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $key => $value) {
                $this->write($key, $value);
            }
        } elseif (is_string($name) && $value !== null) {
            $name = $this->middleware->inputName($name);
            if ($this->struct->hasField($name)) {
                $this->data[$name] = $value;
            } else {
                throw new SQLException(sprintf('table `%s` has no field `%s`', $this->struct->getName(), $name));
            }
        } else {
            $this->data = $name;
        }
        return $this;
    }

    /**
     * 设置影响行数
     *
     * @return $this
     */
    public function wantRows()
    {
        $this->returnType = WriteStatement::RET_ROWS;
        return $this;
    }

    /**
     * 设置返回是否成功
     *
     * @return $this
     */
    public function wantOk()
    {
        $this->returnType = WriteStatement::RET_BOOL;
        return $this;
    }

    /**
     * 设置返回ID
     *
     * @return $this
     */
    public function wantId()
    {
        $this->returnType = WriteStatement::RET_LAST_INSERT_ID;
        return $this;
    }

    /**
     * 删除
     *
     * @return $this
     */
    public function delete()
    {
        $this->delete = true;
        return $this;
    }

    /**
     * 条件查询
     *
     * @param string|array $where
     * @param array $args
     * @return $this
     * @throws SQLException
     */
    public function where($where, ...$args)
    {
        if (is_string($where)) {
            if (is_array($args[0])) {
                $whereParameter = $args[0];
                $this->whereCondition($where, $whereParameter);
            } else {
                list($string, $array) = $this->prepareQueryMark($where, $args);
                $this->whereCondition($string, $array);
            }
        } else {
            $this->aliasKeyField($where);
            $this->arrayWhereCondition($where, $args[0] ?? []);
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
     * 获取字符串
     *
     * @return Query
     */
    protected function prepareQuery(): Query
    {
        if ($this->whereCondition !== null) {
            if ($this->delete === false && is_array($this->data)) {
                list($updateSet, $upbinder) = $this->prepareUpdateSet($this->data);
                $this->binder = array_merge($this->binder, $upbinder);
                $string = "UPDATE {$this->table} SET {$updateSet} WHERE {$this->whereCondition}";
                return new Query($string, $this->binder);
            } elseif ($this->delete === false && is_string($this->data)) {
                $updateSet = trim($this->data);
                $string = "UPDATE {$this->table} SET {$updateSet} WHERE {$this->whereCondition}";
                return new Query($string, $this->binder);
            } else {
                $string = "DELETE FROM {$this->table} WHERE {$this->whereCondition}";
                return new Query($string, $this->binder);
            }
        } else {
            return $this->parepareInsert($this->data);
        }
    }

    /**
     * 字符串式绑定
     *
     * @param string $where
     * @param array $whereParameter
     * @return void
     * @throws SQLException
     */
    protected function whereCondition(string $where, array $whereParameter)
    {
        list($this->whereCondition, $whereBinder) = $this->prepareWhereString($where, $whereParameter);
        $this->binder = array_merge($this->binder, $whereBinder);
    }

    /**
     * 数组条件式绑定
     *
     * @param array $where
     * @param array $whereParameter
     * @return void
     * @throws SQLException
     */
    protected function arrayWhereCondition(array $where, array $whereParameter)
    {
        list($this->whereCondition, $wherebinder) = $this->parepareWhere($where);
        $this->binder = array_merge($this->binder, $wherebinder);
        foreach ($whereParameter as $key => $value) {
            $this->binder[] = new Binder($key, $value);
        }
    }

    /**
     * 准备插入语句
     *
     * @param array $data
     * @return Query
     */
    public function parepareInsert(array $data): Query
    {
        $names = [];
        $binds = [];
        $this->binder;
        foreach ($data as $name => $value) {
            $_name = Binder::index($name);
            $this->binder[] = new Binder($_name, $value, $name);
            $names[] = "`{$name}`";
            $binds[] = ":{$_name}";
        }
        $i_name = implode(',', $names);
        $i_bind = implode(',', $binds);
        return new Query(sprintf("INSERT INTO %s (%s) VALUES (%s)", $this->table, $i_name, $i_bind), $this->binder);
    }
}
