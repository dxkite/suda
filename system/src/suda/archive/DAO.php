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
 * @version    1.2.4
 */
namespace suda\archive;

use suda\core\Query;

class DAO
{
    protected $fields=[];
    protected $primaryKey=null;
    protected $tableName;

    public function __construct()
    {
        // 读取类名作为表名
        // TableName    => table_name
        // TableNameDAO => table_name
        $this->tableName=trim(strtolower(preg_replace('/([A-Z])/','_$1',preg_replace('/^.+\\\\/','',preg_replace('/DAO$/','',get_class($this))))),'_');
        // 默认ID为表主键
        $this->primaryKey='id';
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
    public function updataByPrimaryKey($value, $values)
    {
        if (is_array($values) && !$this->checkFields(array_keys($values))) {
            return false;
        }
        return Query::updata($this->getTableName(), $values, [$this->getPrimaryKey()=>$value]);
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
    public function updata($values, $where)
    {
        if (is_array($values) && !$this->checkFields(array_keys($values))) {
            return false;
        }
        if (is_array($where) && !$this->checkFields(array_keys($where))) {
            return false;
        }
        return Query::updata($this->getTableName(), $values, $where);
    }

    /**
     * 根据条件删除列
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
}
