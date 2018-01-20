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

use PDO;
use PDOException;
use suda\core\Config;
use suda\core\Storage;
use suda\exception\SQLException;

// 数据库查询方案
class SQLQuery
{
    protected static $queryCount=0;
    protected static $times=0;
    protected static $pdo=null;
    protected static $prefix=null;
    protected static $transaction = 0;

    protected $object;

    protected $stmt=null;
    // 查询语句
    protected $query=null;
    // 模板值
    protected $values=null;
    protected $scroll=false;
    // 使用的数据库
    protected $database=null;
    protected $dbchange=false;
   

    // TODO :  支持超大查询 max_allowed_packet

    public function __construct(string $query, array $binds=[], bool $scroll=false)
    {
        self::connectPdo();
        $this->query=$query;
        $this->values=$binds;
        $this->scroll=$scroll;
        $this->object=null;
    }

    public function fetch(int $fetch_style = PDO::FETCH_ASSOC)
    {
        if ($this->stmt) {
            return $this->stmt->fetch($fetch_style);
        } else {
            if (self::lazyQuery($this->query, $this->values)) {
                if ($data=$this->stmt->fetch($fetch_style)){
                    return self::__outputRowTransfrom($data);
                }
            }
        }
        return false;
    }

    public function fetchObject(string $class='stdClass')
    {
        if ($this->stmt) {
            return $this->stmt->fetchObject($class);
        } else {
            if (self::lazyQuery($this->query, $this->values)) {
                return $this->stmt->fetchObject($class);
            }
        }
        return false;
    }
    
    public function fetchAll(int $fetch_style = PDO::FETCH_ASSOC)
    {
        if (self::lazyQuery($this->query, $this->values)) {
            if ($data=$this->stmt->fetchAll($fetch_style)){
                return self::__outputRowsTransfrom($data);
            }
        }
        return false;
    }
    
    public function exec():int
    {
        if (self::lazyQuery($this->query, $this->values)) {
            return $this->stmt->rowCount();
        }
        return 0;
    }

    public function values(array $values)
    {
        $this->values=array_merge($this->values, $values);
        return $this;
    }

    public function query(string $query, array $array=[])
    {
        $this->query=$query;
        $this->values=$array;
        $this->stmt=null;
        return $this;
    }
    
    public function use(string $name=null)
    {
        $this->database=$name;
        $this->dbchange=true;
        return $this;
    }
    // 获取错误
    public function error()
    {
        if ($this->stmt) {
            return $this->stmt->errorInfo();
        }
        return false;
    }

    public function erron()
    {
        if ($this->stmt) {
            return $this->stmt->errorCode();
        }
        return false;
    }

    public static function lastInsertId(string $name=null)
    {
        if (is_null($name)) {
            return self::$pdo->lastInsertId();
        } else {
            return self::$pdo->lastInsertId($name);
        }
        return false;
    }

    // 事务系列
    public static function begin()
    {
        return self::beginTransaction();
    }

    // 事务系列
    public static function beginTransaction()
    {
        self::connectPdo();
        static::$transaction ++;
        if (static::$transaction == 1) {
            self::$pdo->beginTransaction();
        }
    }
    
    public static function commit()
    {
        self::connectPdo();
        if (static::$transaction == 1) {
            self::$pdo->commit();
        }
        static::$transaction--;
    }

    public static function rollBack()
    {
        self::connectPdo();
        if (static::$transaction == 1) {
            static::$transaction=0;
            self::$pdo->rollBack();
        } else {
            static::$transaction--;
        }
    }

    public function quote($string)
    {
        return self::$pdo->quote($string);
    }

    public function arrayQuote(array $array)
    {
        $temp = array();
        foreach ($array as $value) {
            $temp[] = is_int($value) ? $value : self::$pdo->quote($value);
        }
        return implode($temp, ',');
    }

    public static function getRuninfo()
    {
        return ['times'=>self::$times,'counts'=>self::$queryCount];
    }

    private function __SqlPrefix(string $query)
    {
        return preg_replace('/#{(\S+?)}/', self::$prefix.'$1', $query);
    }

    protected function lazyQuery(string $query, array $array=[])
    {
        $query=self::__SqlPrefix($query);
        $debug=debug_backtrace();
        // 调整数据表
        if ($this->database && $this->dbchange) {
            if (self::$pdo->query('USE '.$this->database)) {
                $this->dbchange=false;
                $this->database=null;
            } else {
                throw new SQLException(__('could not select database:%s, please check the table if exist.', $this->database), 0, E_ERROR, $debug[1]['file'], $debug[1]['line']);
            }
        } elseif (is_null($this->database)) {
            $database=Config::get('database.name');
            if ($database) {
                if (self::$pdo->query('USE '.$database)) {
                    $this->database=$database;
                } else {
                    debug()->warning(__('could not select database:%s, maybe you should create database.', $database), 0, E_ERROR, $debug[1]['file'], $debug[1]['line']);
                }
            } else {
                throw new SQLException(__('make sure you have set database info'), 0, E_ERROR, $debug[1]['file'], $debug[1]['line']);
            }
        }

        if ($this->scroll) {
            $stmt=self::$pdo->prepare($query, [PDO::ATTR_CURSOR=>PDO::CURSOR_SCROLL]);
        } else {
            $stmt=self::$pdo->prepare($query);
        }
        foreach ($array as $key=> $value) {
            $key=':'.ltrim($key, ':');
            if (is_array($value)) {
                list($value, $type) =$value;
            } else {
                if (is_null($value)) {
                    $type=PDO::PARAM_NULL;
                } elseif (is_bool($value)) {
                    $type=PDO::PARAM_BOOL;
                } elseif (is_numeric($value)) {
                    $type=PDO::PARAM_INT;
                } else {
                    $type=PDO::PARAM_STR;
                }
            }
            $stmt->bindValue($key, self::__inputFieldTransfrom($key, $value), $type);
        }

        $markstring='query '.$stmt->queryString;
        debug()->time($markstring);
        $return=$stmt->execute();
        self::$times+=debug()->timeEnd($markstring);
        self::$queryCount++;
        if ($return) {
            if (Config::get('debug')) {
                debug()->debug($stmt->queryString, $this->values);
            }
        } else {
            debug()->warning($stmt->errorInfo()[2].':'.$stmt->queryString, $this->values);
            if (!conf('database.ignoreError', false)) {
                throw (new SQLException($stmt->errorInfo()[2], intval($stmt->errorCode()), E_ERROR, $debug[1]['file'], $debug[1]['line']))->setSql($stmt->queryString)->setBinds($this->values);
            }
        }
        $this->stmt=$stmt;
        return $return;
    }

    protected static function connectPdo()
    {
        // 链接数据库
        if (!self::$pdo) {
            $pdo='mysql:host='.Config::get('database.host', 'localhost').';charset='.Config::get('database.charset', 'utf8').';port='.Config::get('database.port', 3306);
            self::$prefix=Config::get('database.prefix', '');
            try {
                debug()->time('connect database');
                self::$pdo = new PDO($pdo, Config::get('database.user', 'root'), Config::get('database.passwd', ''));
                debug()->timeEnd('connect database');
            } catch (PDOException $e) {
                throw new SQLException('connect database error:'.$e->getMessage(), $e->getCode(), E_ERROR, __FILE__, __LINE__, $e);
            }
        }
    }
    
    /**
     * 添加列处理类
     *
     * @param [type] $object
     * @return void
     */
    public function object($object){
        $this->object=$object;
        return $this;
    }

    protected function __dataTransfrom(string $name, string $fieldName, $inputData)
    {
        $methodName='_'.$name.ucfirst($fieldName).'Field';
        if ($this->object) {
            if (method_exists($this->object, $methodName)) {
                $method = new \ReflectionMethod($this->object,$methodName);
                if ($method->isPrivate() || $method->isProtected()) {
                    $method->setAccessible(true);
                }
                $inputData= $method->invokeArgs($this->object,[$inputData]);
            }
        }
        return $inputData;
    }

    private function __inputFieldTransfrom(string $name, $inputData)
    {
        return self::__dataTransfrom('input', $name, $inputData);
    }

    private  function __outputRowsTransfrom(array $inputRows)
    {
        foreach ($inputRows as $id=>$inputData) {
            foreach ($inputData as $fieldName => $fieldData) {
                $inputRows[$id][$fieldName]=self::__dataTransfrom('output', $fieldName, $fieldData);
            }
        }
        return $inputRows;
    }

    private  function __outputRowTransfrom(array $inputData)
    {
        foreach ($inputData as $fieldName => $fieldData) {
            $inputData[$fieldName]=self::__dataTransfrom('output', $fieldName, $fieldData);
        }
        return $inputData;
    }
}
