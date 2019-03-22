<?php
namespace suda\orm\statement;

use suda\orm\Binder;
use suda\orm\TableStruct;
use suda\orm\statement\Statement;
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
    public function __construct(string $rawTableName, TableStruct $struct)
    {
        $this->type = self::WRITE;
        $this->struct = $struct;
        $this->table = $rawTableName;
    }

    /**
     * 写数据
     *
     * @param string|array $name
     * @param array $value
     * @return self
     */
    public function write($name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $key => $value) {
                $this->write($key, $value);
            }
        } else {
            if ($this->struct->getFields()->hasField($name)) {
                $this->data[$name] = $value;
            } else {
                throw new SQLException(\sprintf('table has no fields %s', $this->struct->getName()));
            }
        }
        return $this;
    }

    /**
     * 返回影响行数
     *
     * @return self
     */
    public function effectRows() {
        $this->returnType = WriteStatement::RET_ROWS;
        return $this;
    }

    /**
     * 返回影响行数
     *
     * @return self
     */
    public function isOk() {
        $this->returnType = WriteStatement::RET_BOOL;
        return $this;
    }

    /**
     * 返回影响行数
     *
     * @return self
     */
    public function getId() {
        $this->returnType = WriteStatement::RET_LAST_INSERT_ID;
        return $this;
    }

    /**
     * 删除
     *
     * @return self
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
     * @return self
     */
    public function where($where, array $whereParameter = [])
    {
        if (\is_string($where)) {
            $this->whereCondition($where, $whereParameter);
        } else {
            $this->arrayWhereCondition($where, $whereParameter);
        }
        return $this;
    }

    /**
     * 获取字符串
     *
     * @return void
     */
    public function prepare()
    {
        if ($this->whereCondition !== null) {
            if ($this->delete === false) {
                list($updateSet, $upbinder) = $this->prepareUpdateSet($this->data);
                $this->binder = array_merge($this->binder, $upbinder);
                $this->string = "UPDATE {$this->table} SET {$updateSet} WHERE {$this->whereCondition}";
            } else {
                $this->string = "DELETE FROM {$this->table} WHERE {$this->whereCondition}";
            }
        } else {
            $this->parepareInsert($this->data);
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
        $this->whereCondition = $where;
        foreach ($whereParameter as $key => $value) {
            $this->binder[] = new Binder($key, $value);
        }
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
     * @return void
     */
    public function parepareInsert(array $data)
    {
        $names = [];
        $binds = [];
        foreach ($data as $name => $value) {
            $_name = Binder::index($name);
            $this->binder[] = new Binder($_name, $value, $name);
            $names[] = "`{$name}`";
            $binds[] = ":{$_name}";
        }
        $i_name = \implode(',', $names);
        $i_bind = \implode(',', $binds);
        $this->string = "INSERT INTO {$this->table} ({$i_name}) VALUES ({$i_bind})";
    }



}
