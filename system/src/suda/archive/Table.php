<?php
/**
 * Suda FrameWork
 *
 * An open source application development framework for PHP 7.2.0 or newer
 *
 * Copyright (c)  2017-2018 DXkite
 *
 * @category   PHP FrameWork
 * @package    Suda
 * @copyright  Copyright (c) DXkite
 * @license    MIT
 * @link       https://github.com/DXkite/suda
 * @version    since 1.2.10
 */

namespace suda\archive;

/**
 * 数据表抽象对象
 *
 * 用于提供对数据表的操作
 */
abstract class Table extends TableAccess
{
    // 数据库语句
    protected $statement;
    /**
     * 需求类型
     *
     * @var array|null
     */
    protected $wants = null;
    protected $orderField=null;
    protected $order=null;

    const ORDER_ASC=0;
    const ORDER_DESC=1;

    public function __construct(string $tableName, Connection $connection =null)
    {
        parent::__construct($tableName, $connection);
        $this->statement  = new SQLStatementPrepare($this->connection, $this);
    }

    /**
     * 插入一行记录
     * @example
     *
     * 如果数据表中有name字段和value字段，那么可以通过如下方式插入一条记录
     *
     * ```php
     * $table->insert(['name'=>$name,'value'=>$value]);
     * ```
     *
     * @param array $values 待插入的值
     * @return int 插入影响的行数
     */
    public function insert(array $values):int
    {
        $this->checkFields(array_keys($values));
        return $this->statement->insert($this->getTableName(), $values);
    }

    /**
     * 按照表顺序插入一行记录
     *
     * @example
     *
     * 如果数据表中有name字段和value字段，且表结构如下
     *
     * | name | value |
     * |------|--------|
     * | dxkite| unlimit |
     *
     * 则可以通过如下插入一条语句
     *
     * ```php
     * $table->insertValue($name,$value);
     * ```
     * @param [type] $values 待插入的值
     * @return integer 插入影响的行数
     */
    public function insertValue($values):int
    {
        $values=func_get_args();
        $insert=[];
        foreach ($this->getFields() as $field) {
            $value=array_shift($values);
            if (!is_null($value)) {
                $insert[$field]=$value;
            }
        }
        return $this->statement->insert($this->getTableName(), $insert);
    }

    /**
     * 通过主键查找元素
     * 主键的值可以为关联数组或单独的一个值
     * 查询成功返回查询成功的列，失败返回null
     *
     * @example
     * 主键的值为不定量，有时候有多个主键
     *
     * #### 单主键查询
     * 当用户表中只定义了一个主键的时候
     *
     * ```php
     * $table->getByPrimaryKey($key);
     * ```
     *
     * #### 多主键查询
     *
     * 当用户表中只定义了多个主键的时候
     *
     * ```php
     * $table->getByPrimaryKey(['key1'=>$key1,'key2'=>$key2]);
     * ```
     *
     * @param mixed $value 主键的值
     * @return array|null
     */
    public function getByPrimaryKey($value):?array
    {
        return $this->statement->where($this->getTableName(), $this->getWants(), $this->checkPrimaryKey($value))->object($this)->fetch()?:null;
    }


    /**
     * 通过主键更新元素
     *
     * @example
     * 主键的值为不定量，有时候有多个主键
     *
     * #### 单主键操作
     * 当用户表中只定义了一个主键的时候
     *
     * ```php
     * $table->updateByPrimaryKey($key,['name'=>$name,'value'=>$value]);
     * ```
     *
     * #### 多主键操作
     *
     * 当用户表中只定义了多个主键的时候
     *
     * ```php
     * $table->updateByPrimaryKey(['key1'=>$key1,'key2'=>$key2],['name'=>$name,'value'=>$value]);
     * ```
     *
     * @param mixed $value 主键
     * @param array $values 待更新的数据
     * @return integer 影响的行数
     */
    public function updateByPrimaryKey($value,array $values):int
    {
        $this->checkFields(array_keys($values));
        return $this->statement->update($this->getTableName(), $values, $this->checkPrimaryKey($value));
    }
    
    /**
     * 通过主键删除元素
     *
     * @example
     * 主键的值为不定量，有时候有多个主键
     *
     * #### 单主键操作
     * 当用户表中只定义了一个主键的时候
     *
     * ```php
     * $table->deleteByPrimaryKey($key);
     * ```
     *
     * #### 多主键操作
     *
     * 当用户表中只定义了多个主键的时候
     *
     * ```php
     * $table->deleteByPrimaryKey(['key1'=>$key1,'key2'=>$key2]);
     * ```
     *
     * @param mixed $value 主键
     * @return integer 影响的行数
     */
    public function deleteByPrimaryKey($value):int
    {
        return $this->statement->delete($this->getTableName(), $this->checkPrimaryKey($value));
    }

    /**
     * 根据字段搜索
     *
     * @example
     *
     * 搜索的字段必须为字符串
     * 如：
     * 根据name字段搜索值为$name的可能值
     *
     * ```php
     *  $table->search('name',$name);
     * ```
     *
     * 如果想要实现分页效果，可以用如下代码：搜索，取第一页，每页10条数据
     *
     * ```php
     *  $table->search('name',$name,1,10);
     * ```
     *
     * @param string|array $field 搜索的字段
     * @param string $search 搜索列
     * @param integer|null $page 页码
     * @param integer $rows 每页数
     * @return array|null
     */
    public function search($field, string $search, ?int $page=null, int $rows=10):?array
    {
        return $this->statement->search($this->getTableName(), $this->getWants(), $field, $search, [$page, $rows])->object($this)->fetchAll();
    }
    
    /**
     * 搜索指定字段
     *
     *
     * @example
     *
     * 搜索的字段必须为字符串
     * 如：
     * 根据name字段搜索值为$name的可能值，搜索 status=1 的所有记录
     *
     * ```php
     *  $table->searchWhere('name',$name,['status'=>1]);
     * ```
     *
     * 搜索 status=1 的所有记录,如果想要实现分页效果，可以用如下代码：搜索，取第一页，每页10条数据
     *
     * ```php
     *  $table->searchWhere('name',$name,['status'=>1],[], 1,10);
     * ```
     *
     * 如果条件不是等于，则可以用如下：
     * **注意** 如下中第三个参数的 :status 必须与第四个参数的键名对上
     *
     * ```php
     *  $table->searchWhere('name',$name,' status > :status ',['status'=>1]);
     * ```
     *
     * @param string|array $field 搜索字段
     * @param string $search 搜索值
     * @param string|array $where 限制搜索条件
     * @param array $bind 条件值绑定
     * @param integer|null $page 条件页
     * @param integer $rows 页列
     * @param boolean $offset 是否是偏移
     * @return array|null
     */
    public function searchWhere($field, string $search, $where, array $bind=[], ?int $page=null, int $rows=10, bool $offset=false):?array
    {
        $statment = $this->statement;
        list($searchStr, $searchBind)=$statment->prepareSearch($field, $search);
        $whereStr=$statment->prepareWhere($where, $bind);
        return $statment->select($this->getTableName(), $this->getWants(), $whereStr . ' AND ('. $searchStr.') '. $this->genOrderBy(), array_merge($searchBind, $bind), [$page,$rows,$offset])->fetchAll();
    }

    /**
     * 通知搜索指定字段的个数
     *
     * @example
     *
     * 搜索的字段必须为字符串
     * 如：
     * 根据name字段搜索值为$name的可能值，搜索 status=1 的所有记录
     *
     * ```php
     *  $table->searchWhereCount('name',$name,['status'=>1]);
     * ```
     *
     * 如果条件不是等于，则可以用如下：
     * **注意** 如下中第三个参数的 :status 必须与第四个参数的键名对上
     *
     * ```php
     *  $table->searchWhereCount('name',$name,' status > :status ',['status'=>1]);
     * ```
     *
     * @param string|array $field
     * @param string $search
     * @param string|array $where
     * @param array $bind
     * @return integer
     */
    public function searchWhereCount($field, string $search, $where = null, array $bind= []):int
    {
        $statment = $this->statement;
        list($searchStr, $searchBind)=$statment->prepareSearch($field, $search);
        $whereStr=$statment->prepareWhere($where, $bind);
        return $statment->count($this->getTableName(), $whereStr . ' AND ('. $searchStr.') ', array_merge($searchBind, $bind));
    }

    /**
     * 分页列出元素
     *
     * @example
     *
     * 当不填页码的时候，默认列出所有数据
     * 填入页码时列出对应页
     *
     * ```php
     * $table->list(1,10);
     * ```
     *
     * @param integer|null $page  是否分页（页数）
     * @param int $rows 分页的元素个数
     * @param boolean $offset 使用Offset
     * @return array|null
     */
    public function list(?int $page=null, int $rows=10, bool $offset=false):?array
    {
        return $this->statement->where($this->getTableName(), $this->getWants(), '1 '.  $this->genOrderBy(), [], [$page, $rows,$offset])->fetchAll();
    }

    /**
     * 条件列出元素
     *
     * @example
     *
     * 当不填页码的时候，默认列出所有数据
     * 填入页码时列出对应页
     * 使用条件列出：
     *
     * 等值情况
     * ```php
     * $table->list(['status'=>1],[],1,10);
     * ```
     *
     * 特殊情况
     * ```php
     * $table->list('status > :status ',['status'=>1],1,10);
     * ```
     * **注意** :status 必须后面的键名对上
     *
     * @param string|array $where 条件描述
     * @param array $binds 条件附带参数
     * @param integer|null $page 是否分页（页数）
     * @param integer $rows 分页的元素个数
     * @param boolean $offset 使用Offset
     * @return array|null
     */
    public function listWhere($where, array $binds=[], ?int $page=null, int $rows=10, bool $offset=false):?array
    {
        $statment = $this->statement;
        $where_str = $statment->prepareWhere($where, $binds);
        $where= $where_str.' '.$this->genOrderBy();
        return $statment->where($this->getTableName(), $this->getWants(), $where, $binds, [$page, $rows,$offset])->fetchAll();
    }

    /**
     * 根据条件更新列
     *
     * @example
     *
     * 条件可以为键值对也可以为特殊条件
     *
     * **键值对**
     *
     * 更新 ID 为3 的name 为 $name 的值
     *
     * ```php
     * $table->update(['name'=>$name],['id'=>3]);
     * ```
     *
     * **条件**
     *
     * 更新 ID>3 的name 为 $name 的值
     *
     * ```php
     * $table->update(['name'=>$name],'id > :id ',['id'=>3]);
     * ```
     *
     * @param string|array $values 更新的列
     * @param string|array $where 条件区域
     * @param array $bind 扩展条件值
     * @return integer
     */
    public function update($values, $where, array $bind=[]) :int
    {
        if (is_array($where)) {
            $this->checkFields(array_keys($where));
        }
        if (is_array($values)) {
            $this->checkFields(array_keys($values));
        }
        return $this->statement->update($this->getTableName(), $values, $where, $bind);
    }

    /**
     * 选择列
     *
     * @example
     *
     * 相当于 select 语句，返回一个 SQLQuery类
     *
     * 查询：取一列name，当id为2的时候
     *
     * ```php
     * $table->select(['name'],['id'=>2])->fetch();
     * ```
     *
     * 查询：取所有列name，当 status = 2的时候
     *
     * ```php
     * $table->select(['name'],['status'=>2])->fetchAll();
     * ```
     *
     * 查询：取多列，当 id >2 的时候
     *
     * ```php
     * $table->select(['name'],'id > :id',['id'=>2])->fetchAll();
     * ```
     *
     * @param string|array $wants 想要查询的列
     * @param string|array $where 查询条件
     * @param array $whereBinder 查询条件的值
     * @param integer|null $page 分页页码
     * @param integer $row 分页行
     * @param boolean $offset 直接偏移
     * @return RawQuery
     */
    public function select($wants, $where, $whereBinder=[], ?int $page=null, int $row=10, bool $offset=false): RawQuery
    {
        if (is_array($where)) {
            $this->checkFields(array_keys($where));
        }
        if (is_array($wants)) {
            $this->checkFields($wants);
        }
        return $this->statement->where($this->getTableName(), $wants, $where, $whereBinder, [$page,$row,$offset]);
    }

    /**
     * 原始查询查询
     *
     * @example
     *
     * 请尽量避免使用此函数
     * 其中 #{user} 表示user表，加上 #{} 框架会自动处理前缀
     *
     * ```php
     * $table->query('select * from #{user} where id > :id',['id'=>2]);
     * ```
     *
     * 可以使用 @table@ 代指本表
     *
     * ```php
     * $table->query('select * from #{@table@} where id > :id',['id'=>2]);
     * ```
     *
     * @param string $query
     * @param array $binds
     * @param boolean $scroll
     * @return RawQuery
     */
    public function query(string $query, array $binds=[], bool $scroll=false):RawQuery
    {
        $queryString=preg_replace('/@table@/i', $this->getTableName(), $query);
        return (new RawQuery($this->connection, $queryString, $binds, $scroll))->object($this);
    }

    /**
     * 根据条件删除列
     *
     * @example
     *
     * **键值对**
     *
     * 删除 ID 为3的记录
     *
     * ```php
     * $table->update(['id'=>3]);
     * ```
     *
     * **条件**
     *
     * 删除 ID>3  的记录
     *
     * ```php
     * $table->delete('id > :id ',['id'=>3]);
     * ```
     *
     * @param string|array $where 删除条件
     * @param array $binds 条件值绑定
     * @return integer
     */
    public function delete($where, array $binds=[]):int
    {
        if (is_array($where)) {
            $this->checkFields(array_keys($where));
        }
        return $this->statement->delete($this->getTableName(), $where, $binds);
    }

    /**
     * 设置想要的列
     *
     * @param array $fields
     * @return Table
     */
    public function setWants(array $fields=null)
    {
        $this->wants=is_null($fields)?$this->getFields():$fields;
        return $this;
    }


    /**
     * 获取设置了的列
     *
     * @return array
     */
    public function getWants():array
    {
        return is_null($this->wants)?$this->getFields():$this->fields;
    }

    /**
     * 计数
     *
     * @return int
     */
    public function count($where='1', array $binds=[]):int
    {
        return $this->statement->count($this->getTableName(), $where, $binds);
    }


    /**
     * 排序
     *
     * @param string $field
     * @param integer $order
     * @return Table
     */
    public function order(string $field, int $order=self::ORDER_ASC):Table
    {
        $this->orderField=$field;
        $this->order=$order;
        return $this;
    }

    protected static function strify($object)
    {
        if (is_null($object)) {
            return '[NULL]';
        } elseif (is_object($object)) {
            return serialize($object);
        } elseif (is_array($object)) {
            return json_encode($object);
        }
        return $object;
    }

    protected function genOrderBy()
    {
        if (is_null($this->orderField)) {
            return '';
        } else {
            return ' ORDER BY '. $this->orderField  .' '. ($this->order==self::ORDER_ASC?'ASC':'DESC');
        }
    }
}
