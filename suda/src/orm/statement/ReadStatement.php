<?php
namespace suda\orm\statement;

use suda\orm\TableStruct;
use suda\orm\statement\Statement;
use suda\orm\exception\SQLException;
use suda\orm\statement\PrepareTrait;

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

    protected $distinct = '';

    protected $select = '*';

    protected $where = '';

    protected $groupBy = '';

    protected $having = '';

    protected $orderBy = '';

    protected $limit = '';

    /**
     * 创建写
     *
     * @param string $rawTableName
     * @param TableStruct $struct
     */
    public function __construct(string $rawTableName, TableStruct $struct)
    {
        $this->struct = $struct;
        $this->table = $rawTableName;
        $this->type = self::READ;
        $this->fetch = self::FETCH_ONE;
    }

    /**
     * 单独去重复
     *
     * @return self
     */
    public function distinct()
    {
        $this->distinct = 'DISTINCT';
        return $this;
    }

    /**
     * 查询的列
     *
     * @param array|string $want
     * @return self
     */
    public function want($want)
    {
        if (\func_num_args() > 1) {
            $want = \func_get_args();
        }
        $this->select = $this->prepareWants($want);
        return $this;
    }

    /**
     * 条件
     *
     * @param string|array $where
     * @param array $whereBinder
     * @return self
     */
    public function where($where, ...$args)
    {
        if (\is_array($where)) {
            $this->whereArray($where);
        } elseif (is_array($args[0])) {
            $this->whereStringArray($where, $args[0]);
        } else {
            list($string, $array) = $this->prepareQueryMark($where, $args);
            $this->whereStringArray($string, $array);
        }
        return $this;
    }

    protected function whereArray(array $where)
    {
        list($where, $whereBinder) = $this->parepareWhere($where);
        $this->whereStringArray($where, $whereBinder);
    }

    protected function whereStringArray(string $where, array $whereBinder)
    {
        $this->where = 'WHERE '. $where;
        $this->binder = $this->mergeBinder($this->binder, $whereBinder);
    }

    /**
     * 分组
     *
     * @param string $what
     * @return self
     */
    public function groupBy(string $what)
    {
        $this->groupBy = 'GROUP BY '. $what;
        return $this;
    }

    /**
     * 含
     *
     * @param string|array $what
     * @param array $whereBinder
     * @return self
     */
    public function having($what, ...$args)
    {
        if (\is_array($what)) {
            $this->havingArray($what);
        } elseif (is_array($args[0])) {
            $this->havingStringArray($what, $args[0]);
        } else {
            list($string, $array) = $this->prepareQueryMark($what, $args);
            $this->havingStringArray($string, $array);
        }
        return $this;
    }

    protected function havingArray(array $want)
    {
        list($having, $havingBinder) = $this->parepareWhere($want);
        $this->havingStringArray($having, $havingBinder);
    }

    protected function havingStringArray(string $having, array $havingBinder)
    {
        $this->having = 'HAVING '. $having;
        $this->binder = $this->mergeBinder($this->binder, $havingBinder);
    }

    /**
     * 排序
     *
     * @param string $what
     * @param string $order
     * @return self
     */
    public function orderBy(string $what, string $order = 'ASC')
    {
        $this->orderBy = 'ORDER BY '. $what.' '. $order;
        return $this;
    }

    /**
     * 限制
     *
     * @param int $start
     * @param integer $length
     * @return self
     */
    public function limit(int $start, int $length = null)
    {
        $this->limit = 'LIMIT '. $start . ($length !== null ? ',' .$length :'');
        return $this;
    }

    /**
     * 分页
     *
     * @param integer $page
     * @param integer $length
     * @return self
     */
    public function page(int $page, int $length)
    {
        $this->limit(($page - 1) * $length, $length);
        return $this;
    }

    /**
     * 获取字符串
     *
     * @return void
     */
    public function prepare()
    {
        $where = [$this->where,$this->groupBy,$this->having,$this->orderBy,$this->limit];
        $condition = implode(' ', array_filter(array_map('trim', $where), 'strlen'));
        $select = [$this->distinct,$this->select];
        $selection = implode(' ', array_filter(array_map('trim', $select), 'strlen'));
        $this->string = "SELECT {$selection} FROM {$this->table} {$condition}";
    }
}
