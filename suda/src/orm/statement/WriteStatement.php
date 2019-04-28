<?php
namespace suda\orm\statement;

use suda\orm\Binder;
use suda\orm\TableStruct;
use suda\orm\statement\Query;
use suda\orm\statement\Statement;
use suda\orm\middleware\Middleware;
use suda\orm\exception\SQLException;
use suda\orm\statement\PrepareTrait;

class WriteStatement extends Statement
{
    use PrepareTrait;

    /**
     * 数据
     *
     * @var array
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
     */
    public function __construct(string $rawTableName, TableStruct $struct, Middleware $middleware)
    {
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
     */
    public function write($name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $key => $value) {
                $this->write($key, $value);
            }
        } else {
            $name = $this->middleware->inputName($name);
            if ($this->struct->getFields()->hasField($name)) {
                $this->data[$name] = $value;
            } else {
                throw new SQLException(\sprintf('table `%s` has no field `%s`', $this->struct->getName(), $name));
            }
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
     * @param array $whereParameter
     * @return $this
     */
    public function where($where, ...$args)
    {
        if (\is_string($where)) {
            if (\is_array($args[0])) {
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
    protected function prepareQuery():Query
    {
        if ($this->whereCondition !== null) {
            if ($this->delete === false) {
                list($updateSet, $upbinder) = $this->prepareUpdateSet($this->data);
                $binder = array_merge($this->binder, $upbinder);
                $string = "UPDATE {$this->table} SET {$updateSet} WHERE {$this->whereCondition}";
                return new Query($string, $binder);
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
     */
    protected function whereCondition(string $where, array $whereParameter)
    {
        list($this->whereCondition, $wherebinder) = $this->parepareWhereString($where, $whereParameter);
        $this->binder = array_merge($this->binder, $wherebinder);
    }

    /**
     * 数组条件式绑定
     *
     * @param array $where
     * @param array $whereParameter
     * @return void
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
    public function parepareInsert(array $data):Query
    {
        $names = [];
        $binds = [];
        $binder = $this->binder;
        foreach ($data as $name => $value) {
            $_name = Binder::index($name);
            $binder[] = new Binder($_name, $value, $name);
            $names[] = "`{$name}`";
            $binds[] = ":{$_name}";
        }
        $i_name = \implode(',', $names);
        $i_bind = \implode(',', $binds);
        return new Query("INSERT INTO {$this->table} ({$i_name}) VALUES ({$i_bind})", $binder);
    }
}
