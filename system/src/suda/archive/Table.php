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
     * @param array $values 待插入的值
     * @return int 插入影响的行数
     */
    public function insert(array $values)
    {
        if (is_array($values)) {
            $this->checkFields(array_keys($values));
        }
        return Query::insert($this->getTableName(), $values, [], $this);
    }

    /**
     * 插入一行记录
     * @param $values 待插入的值
     * @return void
     */
    public function insertValue($values)
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
     * 查询成功返回查询成功的列，失败返回false
     *
     * @param [type] $value 主键的值
     * @return array|false
     */
    public function getByPrimaryKey($value)
    {
        return Query::where($this->getTableName(), $this->getWants(), $this->checkPrimaryKey($value))->object($this)->fetch()?:false;
    }


    /**
     * 通过主键更新元素
     *
     * @param [type] $value 待更新的数据
     * @param [type] $data 待更新的数据
     * @return counts 更新的行数
     */
    public function updateByPrimaryKey($value, $values)
    {
        if (is_array($values)) {
            $this->checkFields(array_keys($values));
        }
        return Query::update($this->getTableName(), $values, $this->checkPrimaryKey($value), [], $this);
    }
    
    /**
     * 通过主键删除元素
     *
     * @param [type] $value 待更新的数据
     * @return int
     */
    public function deleteByPrimaryKey($value):int
    {
        return Query::delete($this->getTableName(), $this->checkPrimaryKey($value), [], $this);
    }

    
    public function search($field, string $search, int $page=null, int $rows=10)
    {
        if (is_null($page)) {
            return Query::search($this->getTableName(), $this->getWants(), $field, $search)->object($this);
        } else {
            return Query::search($this->getTableName(), $this->getWants(), $field, $search, [$page, $rows])->object($this);
        }
    }

    /**
     * 分页列出元素
     *
     * @param int $page  是否分页（页数）
     * @param int $rows 分页的元素个数
     * @return array|false
     */
    public function list(int $page=null, int $rows=10)
    {
        if (is_null($page)) {
            return Query::where($this->getTableName(), $this->getWants(), '1 '. self::_order())->object($this)->fetchAll();
        } else {
            return Query::where($this->getTableName(), $this->getWants(), '1 '.  self::_order(), [], [$page, $rows])->object($this)->fetchAll();
        }
    }

    /**
     * 条件列出元素
     *
     * @param int $page  是否分页（页数）
     * @param int $rows 分页的元素个数
     * @return array|false
     */
    public function listWhere($where, array $binds=[], int $page=null, int $rows=10)
    {
        $where_str=Query::prepareWhere($where, $binds);
        $where=preg_replace('/WHERE(.+)$/', '$1', $where_str).' '.self::_order();
        if (is_null($page)) {
            return Query::where($this->getTableName(), $this->getWants(), $where, $binds)->object($this)->fetchAll();
        } else {
            return Query::where($this->getTableName(), $this->getWants(), $where, $binds, [$page, $rows])->object($this)->fetchAll();
        }
    }

    /**
     * 根据条件更新列
     *
     * @param [type] $data
     * @param [type] $where
     * @return int
     */
    public function update($values, $where, array $bind=[])
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
     * 根据条件删除列
     *
     * @param [type] $wants
     * @param [type] $where
     * @param [type] $whereBinder
     * @return Query|false
     */
    public function select($wants, $where, $whereBinder=[])
    {
        if (is_array($where)) {
            $this->checkFields(array_keys($where));
        }
        if (is_array($wants)) {
            $this->checkFields($wants);
        }
        $this->checkFields(array_keys($whereBinder));
        return Query::where($this->getTableName(), $wants, $where, $whereBinder)->object($this);
    }

    /**
     * 纯查询
     *
     * @param string $query
     * @param array $binds
     * @param bool $scroll
     * @return void
     */
    public function query(string $query, array $binds=[], bool $scroll=false)
    {
        $queryString=preg_replace('/@table@/i', $this->getTableName(), $query);
        return (new SQLQuery($queryString, $binds, $scroll))->object($this);
    }

    /**
     * 根据条件获取列
     *
     * @param [type] $where
     * @return int
     */
    public function delete($where)
    {
        if (is_array($where)) {
            $this->checkFields(array_keys($where));
        }
        return Query::delete($this->getTableName(), $where, [], $this);
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
     * @return void
     */
    public function order(string $field, int $order=self::ORDER_ASC)
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
     * @return void
     */
    public function truncate()
    {
        return (new SQLQuery('TRUNCATE TABLE `#{'.$this->tableName.'}`;'))->exec();
    }

    /**
     * 删除数据表
     *
     * @return void
     */
    public function drop()
    {
        return (new SQLQuery('DROP TABLE IF EXISTS `#{'.$this->tableName.'}`;'))->exec();
    }

    /**
     * 导出数据到文件
     *
     * @param string $path
     * @return void
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
     * @return void
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
            return (new SQLQuery(base64_decode($data)))->exec();
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
                    $message='primary key  is multipled,check '.$key.' in fields';
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
        
        foreach ($columns as $column) {
            $fields[]=$column['Field'];
            if ($column['Key']==='PRI') {
                $this->primaryKey[]=$column['Field'];
            }
        }
        $this->setFields($fields);
        return true;
    }

    protected function cacheDbInfo()
    {
        $info['fields']=$this->getFields();
        $info['primaryKey']=$this->getPrimaryKey();
        Storage::path(dirname($this->cachePath));
        ArrayHelper::export($this->cachePath, '_fieldinfos', $info);
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
                    $columns.='\''.addslashes($val).'\',';
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
