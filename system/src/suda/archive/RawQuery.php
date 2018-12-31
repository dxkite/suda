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
 * @version    since 1.2.14
 */

namespace suda\archive;

use PDO;
use suda\core\Config;
use suda\exception\SQLException;
use suda\archive\creator\InputValue;
use suda\tool\Command;

/**
 * 数据库查询方案，提供原始查询方案
 *
 */
class RawQuery implements SQLStatement
{
    protected $connection = null;
    
    protected $object;

    protected $stmt=null;

    /**
     * 查询语句
     *
     * @var mixed
     */
    protected $query=null;
    
    /**
     *  模板值
     *
     * @var mixed
     */
    protected $values=null;
    protected $scroll=false;
    
    /**
     * 使用的数据库
     *
     * @var mixed
     */
    protected $database=null;
    protected $dbchange=false;

    /**
     * 构造查询
     *
     * @param string $query
     * @param array $binds
     * @param boolean $scroll
     */
    public function __construct(Connection $connection, string $query='', array $binds=[], bool $scroll=false)
    {
        $this->connection= $connection;
        $this->object=null;
        $this->query($query, $binds, $scroll);
    }

    public function getConnection()
    {
        return $this->connection;
    }
    
    public function setConnection(Connection $connection)
    {
        $this->connection = $connection;
        return $this;
    }

    /**
     * 获取查询结果的一列
     *
     * @param integer $fetch_style 结果集形式
     * @return array|null 查询成功则返回一列查询结果
     */
    public function fetch(int $fetch_style = PDO::FETCH_ASSOC):?array
    {
        if ($this->stmt) {
            if ($data=$this->stmt->fetch($fetch_style)) {
                return $this->__outputRowTransfrom($data);
            }
        } else {
            if ($this->__query($this->query, $this->values)) {
                if ($data=$this->stmt->fetch($fetch_style)) {
                    return $this->__outputRowTransfrom($data);
                }
            }
        }
        return null;
    }


    /**
     * 获取查询结果的一列，并作为类对象
     *
     * @param string $class 类名
     * @return array|null 查询成功则返回一列查询结果
     */
    public function fetchObject(string $class='stdClass'):?array
    {
        if ($this->stmt) {
            return $this->stmt->fetchObject($class);
        } else {
            if ($this->__query($this->query, $this->values)) {
                return $this->__outputObjectTransfrom($this->stmt->fetchObject($class));
            }
        }
        return null;
    }
    
    /**
     * 获取全部的查询结果
     *
     * @param integer $fetch_style 结果集形式
     * @return array|null 查询成功则返回查询结果，否则返回false
     */
    public function fetchAll(int $fetch_style = PDO::FETCH_ASSOC):?array
    {
        if ($this->__query($this->query, $this->values)) {
            if ($data=$this->stmt->fetchAll($fetch_style)) {
                return $this->__outputRowsTransfrom($data);
            }
        }
        return null;
    }
    
    /**
     * 运行SQL语句
     *
     * @return integer 返回影响的数据行数目
     */
    public function exec():int
    {
        if ($this->__query($this->query, $this->values)) {
            return $this->stmt->rowCount();
        }
        return 0;
    }

    /**
     * 生成一个数据输入值
     *
     * @param string $name 列名
     * @param mixed $value 值
     * @param integer $type 类型
     * @return InputValue 输入变量类
     */
    public static function value(string $name, $value, int $type=PDO::PARAM_STR):InputValue
    {
        return new InputValue($name, $value, $type);
    }

    /**
     * SQL语句模板绑定值
     *
     * @param array $values
     * @return SQLStatement|RawQuery
     */
    public function values(array $values)
    {
        $this->values=array_merge($this->values, $values);
        return $this;
    }

    /**
     * 生成一条查询语句
     *
     * @param string $query 查询语句模板
     * @param array $array 查询语句模板值
     * @return SQLStatement|RawQuery
     */
    public function query(string $query, array $array=[], bool $scroll=false)
    {
        $this->query=$query;
        $this->values=$array;
        $this->stmt=null;
        $this->scroll=$scroll;
        return $this;
    }
    
    /**
     * 切换使用的数据表
     *
     * @param string $name
     * @return SQLStatement|RawQuery
     */
    public function use(string $name=null)
    {
        $this->database=$name;
        $this->dbchange=true;
        return $this;
    }
    
    
    /**
     * 获取语句查询错误
     *
     * @return bool|array 错误结果,false获取失败
     */
    public function error()
    {
        if ($this->stmt) {
            return $this->stmt->errorInfo();
        }
        return false;
    }

    /**
     * 获取语句查询错误编号
     *
     * @return bool|array 语句编号结错误结果,false获取失败果
     */
    public function erron()
    {
        if ($this->stmt) {
            return $this->stmt->errorCode();
        }
        return false;
    }

    /**
     * 获取最后一次插入的主键ID（用于自增值
     *
     * @param string $name
     * @return int 获插入ID
     */
    public function lastInsertId(string $name=null)
    {
        if (is_null($name)) {
            return $this->connection->getPdo()->lastInsertId();
        } else {
            return $this->connection->getPdo()->lastInsertId($name);
        }
    }

    /**
     * 事务系列，开启事务
     *
     * @return void
     */
    public function begin()
    {
        $this->beginTransaction();
    }

    /**
     * 事务系列，开启事务
     *
     * @return void
     */
    public function beginTransaction()
    {
        $this->connection->beginTransaction();
    }

    /**
     * 事务系列，提交事务
     *
     * @return void
     */
    public function commit()
    {
        $this->connection->commit();
    }

    /**
     * 事务系列，撤销事务
     *
     * @return void
     */
    public function rollBack()
    {
        $this->connection->rollBack();
    }

    public function quote($string)
    {
        return $this->connection->getPdo()->quote($string);
    }

    public function arrayQuote(array $array)
    {
        $temp = array();
        foreach ($array as $value) {
            $temp[] = is_int($value) ? $value : $this->connection->getPdo()->quote($value);
        }
        return implode(',', $temp);
    }

    private function __SqlPrefix(string $query)
    {
        return preg_replace('/#{(\S+?)}/', $this->connection->prefix.'$1', $query);
    }

    private function __query(string $query, array $array=[])
    {
        if (!conf('database.enable', true)) {
            return false;
        }
        $query=$this->__SqlPrefix($query);
        $debug=debug_backtrace();
        // 调整数据表
        if ($this->database && $this->dbchange) {
            if ($this->connection->getPdo()->query('USE '.$this->database)) {
                $this->dbchange=false;
                $this->database=null;
            } else {
                throw new SQLException(__('could not select database:$0, please check the table if exist.', $this->database), 0, E_ERROR, $debug[1]['file'], $debug[1]['line']);
            }
        } elseif (is_null($this->database)) {
            $database=$this->connection->database;
            if ($database) {
                if ($this->connection->getPdo()->query('USE '.$database)) {
                    $this->database=$database;
                } else {
                    debug()->warning(__('could not select database:$0, maybe you should create database.', $database));
                }
            } else {
                throw new SQLException(__('make sure you have set database info'), 0, E_ERROR, $debug[1]['file'], $debug[1]['line']);
            }
        }

        if ($this->scroll) {
            $stmt=$this->connection->getPdo()->prepare($query, [PDO::ATTR_CURSOR=>PDO::CURSOR_SCROLL]);
        } else {
            $stmt=$this->connection->getPdo()->prepare($query);
        }
        
        foreach ($array as $key=> $value) {
            $bindName=':'.ltrim($key, ':');
            if ($value instanceof InputValue) {
                $data= $this->__inputFieldTransfrom($value->getName(), $value->getValue());
                $stmt->bindValue($bindName, $data, InputValue::bindParam($data));
            } else {
                $stmt->bindValue($bindName, $value, InputValue::bindParam($value));
            }
        }

        $markstring='SQL Query '.$this->connection->id.' "'.$stmt->queryString.'"';
        debug()->time($markstring);
        $return=$stmt->execute();
        $this->connection->countQuery();
        debug()->timeEnd($markstring);
        
        if ($return) {
            if (Config::get('debug')) {
                debug()->debug($stmt->queryString .' '. __('effect $0 rows', $stmt->rowCount()), $this->values);
            }
        } else {
            debug()->warning($stmt->errorInfo()[2].':'.$stmt->queryString, $this->values);
            if (!conf('database.ignore-error', false)) {
                throw (new SQLException($stmt->errorInfo()[2], intval($stmt->errorCode()), E_ERROR, $debug[1]['file'], $debug[1]['line']))->setSql($stmt->queryString)->setBinds($this->values);
            }
        }
        $this->stmt=$stmt;
        return $return;
    }

    /**
     * 添加列处理类
     *
     * @param object $object
     * @return RawQuery
     */
    public function object(object $object)
    {
        $this->object=$object;
        return $this;
    }

    /**
     * 转换函数；统一处理数据库输入输出
     *
     * 只处理InputValue类型的数据
     *
     * @param string $name
     * @param string $fieldName
     * @param mixed $inputData
     * @return mixed
     */
    protected function __dataTransfrom(string $name, string $fieldName, $inputData)
    {
        $methodName='_'.$name.ucfirst($fieldName).'Field';
        if ($this->object) {
            if (method_exists($this->object, '__dataTransfrom')) {
                return Command::_absoluteCall([$this->object,'__dataTransfrom'], func_get_args());
            } elseif (method_exists($this->object, $methodName)) {
                $inputData= Command::_absoluteCall([$this->object,$methodName], [$inputData]);
            }
        }
        return $inputData;
    }

    private function __inputFieldTransfrom(string $name, $inputData)
    {
        return $this->__dataTransfrom('input', $name, $inputData);
    }

    private function __outputDataFilter($rowData)
    {
        $methodName='_outputDataFilter';
        if ($this->object) {
            if (method_exists($this->object, $methodName)) {
                return Command::_absoluteCall([$this->object, $methodName], [$rowData]);
            }
        }
        return $rowData;
    }

    private function __outputFieldTransfrom(string $name, $inputData)
    {
        return $this->__dataTransfrom('output', $name, $inputData);
    }

    private function __outputRowsTransfrom(array $inputRows)
    {
        foreach ($inputRows as $id=>$inputData) {
            $inputRows[$id]=$this->__outputRowTransfrom($inputRows[$id]);
        }
        return $inputRows;
    }

    private function __outputRowTransfrom(array $inputData)
    {
        foreach ($inputData as $fieldName => $fieldData) {
            $inputData[$fieldName]=$this->__outputFieldTransfrom($fieldName, $fieldData);
        }
        return $this->__outputDataFilter($inputData);
    }

    private function __outputObjectTransfrom($object)
    {
        $reflect=new \ReflectionClass($object);
        $props=$reflect->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE);
        foreach ($props as $prop) {
            $prop->setAccessible(true);
            $prop->setValue($object, $this->__outputFieldTransfrom($prop->getName(), $prop->getValue()));
        }
        return $this->__outputDataFilter($object);
    }
}
