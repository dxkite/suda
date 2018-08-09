<?php
/**
 * Suda FrameWork
 *
 * An open source application development framework for PHP 7.0.0 or newer
 *
 * Copyright (c)  2017 DXkite
 *
 * @category   PHP FrameWork
 * @package    Suda
 * @copyright  Copyright (c) DXkite
 * @license    MIT
 * @link       https://github.com/DXkite/suda
 * @version    since 1.2.10
 */

namespace suda\archive;

use suda\archive\creator\Table as TableCreator;
use suda\core\Query;
use suda\core\Storage;
use suda\tool\ArrayHelper;
use suda\exception\TableException;

/**
 * 数据表抽象对象
 *
 * 用于提供对数据表的操作
 *
 */
abstract class Table
{
    protected $fields=[];
    protected $wants;
    protected $primaryKey;
    protected $tableName;
    protected $cachePath;
    protected $creator;

    protected $orderField=null;
    protected $order=null;
    const ORDER_ASC=0;
    const ORDER_DESC=1;

    public function __construct(string $tableName)
    {
        // 默认ID为表主键
        $this->primaryKey[]='id';
        $this->tableName=$tableName;
        $this->cachePath=CACHE_DIR.'/database/fields/'.$this->tableName.'.php';
        // 读取类名作为表名
        self::initTableFields();
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
        if (is_array($values)) {
            $this->checkFields(array_keys($values));
        }
        return Query::insert($this->getTableName(), $values, [], $this);
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
        return Query::insert($this->getTableName(), $insert, [], $this);
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
     * @param [type] $value 主键的值
     * @return array|null
     */
    public function getByPrimaryKey($value):?array
    {
        return Query::where($this->getTableName(), $this->getWants(), $this->checkPrimaryKey($value))->object($this)->fetch()?:null;
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
     * @param [type] $value 主键
     * @param [type] $values 待更新的数据
     * @return integer 影响的行数
     */
    public function updateByPrimaryKey($value, $values):int 
    {
        if (is_array($values)) {
            $this->checkFields(array_keys($values));
        }
        return Query::update($this->getTableName(), $values, $this->checkPrimaryKey($value), [], $this);
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
     * @param [type] $value 主键
     * @return integer 影响的行数
     */
    public function deleteByPrimaryKey($value):int
    {
        return Query::delete($this->getTableName(), $this->checkPrimaryKey($value), [], $this);
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
     * @param [type] $field 搜索的字段
     * @param string $search 搜索列
     * @param integer|null $page 页码
     * @param integer $rows 每页数
     * @return array|null
     */
    public function search($field, string $search, ?int $page=null, int $rows=10):?array
    {
        if (is_null($page)) {
            return Query::search($this->getTableName(), $this->getWants(), $field, $search)->object($this);
        } else {
            return Query::search($this->getTableName(), $this->getWants(), $field, $search, [$page, $rows])->object($this);
        }
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
     * @param [type] $field 搜索字段
     * @param string $search 搜索值
     * @param [type] $where 限制搜索条件
     * @param array $bind 条件值绑定
     * @param integer|null $page 条件页
     * @param integer $rows 页列
     * @param boolean $offset 是否是偏移
     * @return array|null
     */
    public function searchWhere($field, string $search, $where, array $bind=[], ?int $page=null, int $rows=10, bool $offset=false):?array
    {
        list($searchStr, $searchBind)=Query::prepareSearch($field, $search);
        $whereStr=Query::prepareWhere($where, $bind);
        if (is_null($page)) {
            return Query::select($this->getTableName(), $this->getWants(), $whereStr . ' AND ('. $searchStr.') '. self::_order(), array_merge($searchBind, $bind))->object($this);
        }
        return Query::select($this->getTableName(), $this->getWants(), $whereStr . ' AND ('. $searchStr.') '. self::_order(), array_merge($searchBind, $bind), [$page,$rows,$offset])->object($this);
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
     * @param [type] $field
     * @param string $search
     * @param [type] $where
     * @param array $bind
     * @return integer
     */
    public function searchWhereCount($field, string $search , $where, array $bind=[]):int
    {
        list($searchStr, $searchBind)=Query::prepareSearch($field, $search);
        $whereStr=Query::prepareWhere($where, $bind);
        if (is_null($page)) {
            return Query::count($this->getTableName(), $whereStr . ' AND ('. $searchStr.') ', array_merge($searchBind, $bind));
        }
        return Query::count($this->getTableName(), $whereStr . ' AND ('. $searchStr.') ', array_merge($searchBind, $bind), [$page,$rows,$offset]);
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
        if (is_null($page)) {
            return Query::where($this->getTableName(), $this->getWants(), '1 '. self::_order())->object($this)->fetchAll();
        } else {
            return Query::where($this->getTableName(), $this->getWants(), '1 '.  self::_order(), [], [$page, $rows,$offset])->object($this)->fetchAll();
        }
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
     * @param [type] $where 条件描述
     * @param array $binds 条件附带参数
     * @param integer|null $page 是否分页（页数）
     * @param integer $rows 分页的元素个数
     * @param boolean $offset 使用Offset
     * @return array|null
     */
    public function listWhere($where, array $binds=[], ?int $page=null, int $rows=10, bool $offset=false):?array
    {
        $where_str=Query::prepareWhere($where, $binds);
        $where=preg_replace('/WHERE(.+)$/', '$1', $where_str).' '.self::_order();
        if (is_null($page)) {
            return Query::where($this->getTableName(), $this->getWants(), $where, $binds)->object($this)->fetchAll();
        } else {
            return Query::where($this->getTableName(), $this->getWants(), $where, $binds, [$page, $rows,$offset])->object($this)->fetchAll();
        }
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
     * @param [type] $values 更新的列
     * @param [type] $where 条件区域
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
        return Query::update($this->getTableName(), $values, $where, $bind, $this);
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
     * @param [type] $wants 想要查询的列
     * @param [type] $where 查询条件
     * @param array $whereBinder 查询条件的值
     * @param integer|null $page 分页页码
     * @param integer $row 分页行
     * @param boolean $offset 直接偏移
     * @return SQLQuery
     */
    public function select($wants, $where, $whereBinder=[], ?int $page=null, int $row=10, bool $offset=false): SQLQuery
    {
        if (is_array($where)) {
            $this->checkFields(array_keys($where));
        }
        if (is_array($wants)) {
            $this->checkFields($wants);
        }
        if (is_null($page)) {
            return Query::where($this->getTableName(), $wants, $where, $whereBinder)->object($this);
        }
        return Query::where($this->getTableName(), $wants, $where, $whereBinder, [$page,$row,$offset])->object($this);
    }

    /**
     * 原始查询查询
     *
     * @example
     * 
     * 请尽量避免使用此函数
     * 其中 #{user} 表示user表，加上 #{} 框架会自动处理浅醉
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
     * @return SQLQuery
     */
    public function query(string $query, array $binds=[], bool $scroll=false):SQLQuery
    {
        $queryString=preg_replace('/@table@/i', $this->getTableName(), $query);
        return (new SQLQuery($queryString, $binds, $scroll))->object($this);
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
     * @param [type] $where 删除条件
     * @param array $binds 条件值绑定
     * @return integer
     */
    public function delete($where,array $binds=[]):int 
    {
        if (is_array($where)) {
            $this->checkFields(array_keys($where));
        }
        return Query::delete($this->getTableName(), $where, $binds, $this);
    }

    /**
     * 获取主键
     *
     * @return string
     */
    public function getPrimaryKey():array
    {
        return $this->primaryKey;
    }

    /**
     * 设置主键
     *
     * @param array $keys
     * @return void
     */
    public function setPrimaryKey(array $keys)
    {
        $this->primaryKey=$keys;
        return $this;
    }

    /**
     * 设置表名
     *
     * @param string $name
     * @return void
     */
    public function setTableName(string $name)
    {
        $this->tableName;
        return $this;
    }

    /**
     * 获取表名
     *
     * @return string
     */
    public function getTableName():string
    {
        return $this->tableName;
    }

    /**
     * 设置表列
     *
     * @param array $fields
     * @return void
     */
    public function setFields(array $fields=null)
    {
        if (is_null($fields)) {
            self::initTableFields();
            return $this;
        }
        $this->fields=$fields;
        return $this;
    }

    /**
     * 获取全部的列
     *
     * @return array
     */
    public function getFields():array
    {
        return $this->fields;
    }

    /**
     * 设置想要的列
     *
     * @param array $fields
     * @return void
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
        return $this->wants??$this->fields;
    }

    /**
     * 计数
     *
     * @return int
     */
    public function count($where='1', array $binds=[]):int
    {
        return Query::count($this->getTableName(), $where, $binds, $this);
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

    public function createTable()
    {
        // 删除数据表
        $this->drop();
        return self::initFromTable(self::getCreator());
    }
    
    public function getCreateSql():string
    {
        return (string)$this->getCreator();
    }

    public function getCreator()
    {
        if (is_null($this->creator)) {
            $this->creator=$this->onBuildCreator(new TableCreator($this->tableName, 'utf8'));
        }
        return $this->creator;
    }

    public static function begin()
    {
        return SQLQuery::begin();
    }

    public static function commit()
    {
        return SQLQuery::commit();
    }

    public static function rollBack()
    {
        return SQLQuery::rollBack();
    }

    
    /**
     * 清空数据表
     *
     * @return int 返回影响的数据行数目
     */
    public function truncate():int
    {
        return (new SQLQuery('TRUNCATE TABLE `#{'.$this->tableName.'}`;'))->exec();
    }
  
    /**
     * 删除数据表
     *
     * @return int 返回影响的数据行数目
     */
    public function drop():int
    {
        return (new SQLQuery('DROP TABLE IF EXISTS `#{'.$this->tableName.'}`;'))->exec();
    }

    /**
     * 导出数据到文件
     *
     * @param string $path
     * @return bool|int
     */
    public function export(string $path)
    {
        if ($data=$this->getDataString()) {
            $base64=base64_encode($data);
            $sha1=sha1($base64);
            storage()->path(dirname($path));
            return storage()->put($path, $this->tableName.','.time().','.$sha1.',base64;'.$base64);
        }
        return false;
    }

    /**
     * 从导出文件中恢复数据
     *
     * @param string $path
     * @return bool|int
     */
    public function import(string $path)
    {
        if (storage()->exist($path)) {
            try {
                list($head, $data)=explode(';', storage()->get($path));
                list($name, $time, $sha1, $dataType)=explode(',', $head);
            } catch (\Exception $e) {
                return false;
            }
            if (sha1($data)!=$sha1 || $time >time() || $name!=$this->tableName) {
                return false;
            }
            try {
                static::begin();
                $num = (new SQLQuery(base64_decode($data)))->exec();
                static::commit();
                return $num;
            } catch (\Exception $e) {
                static::rollBack();
            }
        }
        return false;
    }
    
    protected function checkPrimaryKey($value)
    {
        if (count($this->primaryKey)===1) {
            return [ $this->primaryKey[0]=>$value];
        } else {
            // 检查主键完整性
            foreach ($this->primaryKey as $key) {
                if (!isset($value[$key])) {
                    $message='primary key  is multipled, check '.$key.' in fields';
                    $debug=debug_backtrace();
                    throw new TableException(__($message), 0, E_ERROR, $debug[1]['file'], $debug[1]['line']);
                }
            }
            return $value;
        }
    }

    /**
     * 检查参数列
     *
     * @param array $values
     */
    protected function checkFields(array $values)
    {
        foreach ($values as $key) {
            if (!in_array($key, $this->fields)) {
                throw new TableException(__('field %s is not exsits in table', $key));
            }
        }
    }

    abstract protected function onBuildCreator($table);
    
    protected function initFromTable(TableCreator $table)
    {
        (new SQLQuery($table))->exec();
        $this->primaryKey=$table->getPrimaryKeyName();
        $this->fields=$table->getFieldsName();
        return true;
    }
    
    protected function initTableFields()
    {
        if (file_exists($this->cachePath) && !conf('debug')) {
            $fieldsinfo=require $this->cachePath;
            $this->setFields($fieldsinfo['fields']);
            $this->setPrimaryKey($fieldsinfo['primaryKey']);
        } else {
            if (!$this->initFromDatabase()) {
                $this->createTable();
            }
            $this->cacheDbInfo();
        }
    }

    protected function initFromDatabase()
    {
        $fields=[];
        $this->primaryKey=[];
        try {
            $columns=(new SQLQuery('show columns from #{'.$this->getTableName().'};'))->fetchAll();
        } catch (\suda\exception\SQLException  $e) {
            return false;
        }
        if (is_array($columns)) {
            foreach ($columns as $column) {
                $fields[]=$column['Field'];
                if ($column['Key']==='PRI') {
                    $this->primaryKey[]=$column['Field'];
                }
            }
            $this->setFields($fields);
            return true;
        }
        return false;
    }

    protected function cacheDbInfo()
    {
        $info['fields']=$this->getFields();
        $info['primaryKey']=$this->getPrimaryKey();
        if (cache()->enable()) {
            Storage::path(dirname($this->cachePath));
            ArrayHelper::export($this->cachePath, '_fieldinfos', $info);
        }
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

    protected function _order()
    {
        if (is_null($this->orderField)) {
            return '';
        } else {
            return ' ORDER BY '. $this->orderField  .' '. ($this->order==self::ORDER_ASC?'ASC':'DESC');
        }
    }

    /**
     * 获取数据SQL字符串
     *
     * @return void
     */
    protected function getDataString()
    {
        $table=$this->tableName;
        $q=new SQLQuery('SELECT * FROM `#{'.$table.'}` WHERE 1;', [], true);
        $columns=(new SQLQuery('SHOW COLUMNS FROM `#{'.$table.'}`;'))->fetchAll();
        $key='(';
        foreach ($columns  as $column) {
            $key.='`'.$column['Field'].'`,';
        }
        $key=rtrim($key, ',').')';
        if ($q) {
            $sqlout='INSERT INTO `#{'.$table.'}` '.$key.' VALUES ';
            $first=true;
            while ($values=$q->fetch()) {
                $sql='';
                if ($first) {
                    $first=false;
                } else {
                    $sql.=',';
                }
                $sql.='(';
                $columns='';
                foreach ($values as $val) {
                    if (is_null($val)) {
                        $columns.='NULL,';
                    } else {
                        $columns.='\''.addslashes($val).'\',';
                    }
                }
                $columns=rtrim($columns, ',');
                $sql.= $columns;
                $sql.=')';
                $sqlout.=$sql;
            }
            if ($first) {
                return false;
            }
            return $sqlout;
        }
        return false;
    }
}
