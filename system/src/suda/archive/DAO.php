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

use suda\core\Query;
use suda\core\Storage;
use suda\tool\ArrayHelper;
use suda\exception\DAOException;

class DAO
{
    protected $fields=[];
    protected $wants;

    /**
     * 验证：类型，长度，正则
     * fieldname=>verify_type,error_message
     * @var array
     */
    protected $field_check=[];

    protected $primaryKey=null;
    protected $tableName;

    public function __construct(string $tableName)
    {
        // 默认ID为表主键
        $this->primaryKey='id';
        $this->tableName=$tableName;
        // 读取类名作为表名
        self::initTableFields();
    }


    /**
     * 插入行
     * @param array $values 待插入的值
     * @return void
     */
    public function insert(array $values)
    {
        if (is_array($values) && !($this->checkFields(array_keys($values)) && $this->checkFieldsType($values))) {
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
            if (!is_null($value)) {
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
        return Query::where($this->getTableName(), $this->getWants(), [$this->getPrimaryKey()=>$value])->fetch()?:false;
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
        if (is_array($values) && !($this->checkFields(array_keys($values)) && $this->checkFieldsType($values))) {
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
     * 分页列出元素
     *
     * @param int $page  是否分页（页数）
     * @param int $rows 分页的元素个数
     * @return array|false
     */
    public function list(int $page=null, int $rows=10)
    {
        if (is_null($page)) {
            return Query::where($this->getTableName(), $this->getWants())->fetchAll();
        } else {
            return Query::where($this->getTableName(), $this->getWants(), '1', [], [$page, $rows])->fetchAll();
        }
    }

    /**
     * 条件列出元素
     *
     * @param int $page  是否分页（页数）
     * @param int $rows 分页的元素个数
     * @return array|false
     */
    public function listWhere($where, int $page=null, int $rows=10)
    {
        if (is_null($page)) {
            return Query::where($this->getTableName(), $this->getWants(), $where)->fetchAll();
        } else {
            return Query::where($this->getTableName(), $this->getWants(), $where, [], [$page, $rows])->fetchAll();
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
        if (is_array($values) && !($this->checkFields(array_keys($values)) && $this->checkFieldsType($values))) {
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

    public function setWants(array $fields=null)
    {
        $this->wants=is_null($fields)?$this->getFields():$fields;
        return $this;
    }

    public function getWants():array
    {
        return $this->wants??$this->fields;
    }
    /**
     * 检查参数列
     *
     * @param array $values
     * @return bool
     */
    protected function checkFields(array $values):bool
    {
        foreach ($values as $key) {
            if (!in_array($key, $this->fields)) {
                throw new DAOException(__('field %s is not exsits in table', $key));
            }
        }
        return true;
    }

    /**
     * 检查参数列
     *
     * @param array $values
     * @return bool
     */
    protected function checkFieldsType($values):bool
    {
        $check= $this->field_check;
        $keys=array_keys($check);
        foreach ($keys as $key) {
            if (isset($values[$key]) && !self::checkValueType($this->field_check[$key][0], $values[$key])) {
                $message=str_replace(['{key}','{value}','{check}'], [$key,self::strify($values[$key]),$this->field_check[$key][0]], $this->field_check[$key][1]??'field {key} value {value} type is not valid');
                $debug=debug_backtrace();
                throw new DAOException(__($message), 0, E_ERROR, $debug[1]['file'], $debug[1]['line']);
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


    protected function checkValueType(string $check, $value)
    {
        static $type2name=[
            'int'=>'numeric',
            'integer'=>'numeric',
            'boolean'=>'bool',
            'bool'=>'bool',
            'double'=>'float',
            'float'=>'float',
            'string'=>'string',
            'array'=>'array',
            'object'=>'object',
            'resource'=>'resource',
        ];
        // _D()->info($check, $value);
        // 类型检测
        if (preg_match('/^'. implode('|', array_keys($type2name)) .'$/', $check)) {
            // _D()->info('type check');
            $name='is_'.$type2name[$check];
            if (!$name($value)) {
                return false;
            }
            // 长度检测
        } elseif (preg_match('/^(\d+)(?:\,(\d+))?$/', $check, $match)) {
            // _D()->info('length check',$match);
            if (isset($match[2])) {
                $min=$match[1];
                $max=$match[2];
                if (strlen($value) < $min || strlen($value) > $max) {
                    return false;
                }
            } elseif (strlen($value)!==intval($match[1])) {
                return false;
            }
            // 正则检测
        } elseif (preg_match('/^[\/](\S+)[\/]([imsxeADSXUuJ]+)?$/', $check)) {
            // _D()->info('preg check');
            if (!preg_match($check, $value)) {
                return false;
            }
        }
        return true;
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
}
