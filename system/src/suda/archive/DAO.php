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
 * @version    since 1.2.4
 */
namespace suda\archive;

use suda\core\{Query,Storage};
use suda\tool\ArrayHelper;

class DAO
{
    protected $fields=[];
    protected $primaryKey=null;
    protected $tableName;

    public function __construct(string $tableName)
    {
        // 默认ID为表主键
        $this->primaryKey='id';
        $this->tableName=$tableName;
        // 读取类名作为表名
        // TableName    => table_name
        // TableNameDAO => table_name
        // if (is_null($tableName)) {
        //     $this->tableName=trim(strtolower(preg_replace('/([A-Z])/', '_$1', preg_replace('/^.+\\\\/', '', preg_replace('/DAO$/', '', get_class($this))))), '_');
        // }else{
        //     $this->tableName=$tableName;
        // }
        self::initTableFields();
    }


    /**
     * 插入行
     * @param array $values 待插入的值
     * @return void
     */
    public function insert(array $values)
    {
        if (is_array($values) && !$this->checkFields(array_keys($values))) {
            return false;
        }
        return Query::insert($this->getTableName(), $values);
    }

    /**
     * 插入行
     * @param $values 待插入的值
     * @return void
     */
    public function insertValue($values)
    {
        $values=func_get_args();
        $insert=[];
        foreach ($this->getFields() as $field) {
            $value=array_shift($values);
            if(!is_null($value)){
                $insert[$field]=$value;
            }
        }
        return Query::insert($this->getTableName(), $insert);
    }

    /**
     * 通过主键查找元素
     *
     * @param [type] $value 主键的值
     * @return array|false
     */
    public function getByPrimaryKey($value)
    {
        if (is_array($values) && !$this->checkFields(array_keys($values))) {
            return false;
        }
        return Query::where($this->getTableName(), $this->getFields(), [$this->getPrimaryKey()=>$value])->fetch()?:false;
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
        if (is_array($values) && !$this->checkFields(array_keys($values))) {
            return false;
        }
        return Query::update($this->getTableName(), $values, [$this->getPrimaryKey()=>$value]);
    }
    
    /**
     * 通过主键删除元素
     *
     * @param [type] $value 待更新的数据
     * @return int
     */
    public function deleteByPrimaryKey($value):int
    {
        return Query::delete($this->getTableName(), [$this->getPrimaryKey()=>$value]);
    }

    /**
     * 列出全部元素
     *
     * @param int $page  是否分页（页数）
     * @param int $rows 分页的元素个数
     * @return array|false
     */
    public function list(int $page=null, int $rows=10)
    {
        if (is_null($page)) {
            return Query::where($this->getTableName(), $this->getFields())->fetchAll();
        } else {
            return Query::where($this->getTableName(), $this->getFields(), '1', [], [$page, $rows])->fetchAll();
        }
    }

    /**
     * 根据条件更新列
     *
     * @param [type] $data
     * @param [type] $where
     * @return int
     */
    public function update($values, $where)
    {
        if (is_array($values) && !$this->checkFields(array_keys($values))) {
            return false;
        }
        if (is_array($where) && !$this->checkFields(array_keys($where))) {
            return false;
        }
        return Query::update($this->getTableName(), $values, $where);
    }


    /**
     * 根据条件删除列
     *
     * @param [type] $wants
     * @param [type] $where
     * @return int
     */
    public function select($wants, $where)
    {
        if (is_array($where) && !$this->checkFields(array_keys($where))) {
            return false;
        }
        if (is_array($wants) && !$this->checkFields($wants)) {
            return false;
        } elseif (is_string($wants)) {
            if (!in_array($wants, $this->fields)) {
                return false;
            }
        }
        return Query::where($this->getTableName(), $wants, $where);
    }
        
    /**
     * 根据条件删除列
     *
     * @param [type] $wants
     * @param [type] $where
     * @return int
     */
    public function query(string $query, array $binds=[], bool $scroll=false)
    {
        return new SQLQuery($query, $binds, $scroll);
    }

    /**
     * 根据条件获取列
     *
     * @param [type] $where
     * @return int
     */
    public function delete($where)
    {
        if (is_array($where) && !$this->checkFields(array_keys($where))) {
            return false;
        }
        return Query::delete($this->getTableName(), $where);
    }

    /**
     * 获取主键
     *
     * @return string
     */
    public function getPrimaryKey():string
    {
        return $this->primaryKey;
    }

    /**
     * 设置主键
     *
     * @param string $keyname
     * @return void
     */
    public function setPrimaryKey(string $keyname)
    {
        $this->primaryKey=$keyname;
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
    public function setFields(array $fields)
    {
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
     * 检查参数列
     *
     * @param array $values
     * @return bool
     */
    public function checkFields(array $values):bool
    {
        foreach ($values as $key) {
            if (!in_array($key, $this->fields)) {
                return false;
            }
        }
        return true;
    }


    /**
     * 计数
     *
     * @return int
     */
    public function count():int
    {
        return Query::count($this->getTableName());
    }

    
    private function initTableFields()
    {
        // 使用DTO文件
        $path=TEMP_DIR.'/db/fields/'.$this->tableName.'.php';
        if (file_exists($path)) {
            $fieldsinfo=require $path;
            $this->setFields(array_keys($fieldsinfo['fields']));
            $this->setPrimaryKey($fieldsinfo['primaryKey']);
        } else {
            $fields=[];
            $columns=(new SQLQuery('show columns from #{'.$this->getTableName().'};'))->fetchAll();
            foreach ($columns as $column) {
                $fields[$column['Field']]=$column['Type'];
                if ($column['Key']==='PRI') {
                    $this->setPrimaryKey($column['Field']);
                }
            }
            $this->setFields(array_keys($fields));
            $info['fields']=$fields;
            $info['primaryKey']=$this->getPrimaryKey();
            Storage::path(dirname($path));
            ArrayHelper::export($path, '_fieldinfos', $info);
        }
    }
}
