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
    public static $queryCount=0;
    protected static $pdo=null;
    protected static $prefix=null;
    protected $stmt=null;
    // 查询语句
    protected $query=null;
    // 模板值
    protected $values=null;
    protected $scroll=false;
    // 使用的数据库
    protected $database=null;
    protected $dbchange=false;
    protected static $good=true;

    // TODO :  支持超大查询 max_allowed_packet

    public function __construct(string $query, array $binds=[], bool $scroll=false)
    {
        self::connectPdo();
        $this->query=$query;
        $this->values=$binds;
        $this->scroll=$scroll;
    }

    public function fetch(int $fetch_style = PDO::FETCH_ASSOC)
    {
        if ($this->stmt) {
            return $this->stmt->fetch($fetch_style);
        } else {
            if (self::lazyQuery($this->query, $this->values)) {
                return $this->stmt->fetch($fetch_style);
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
            return $this->stmt->fetchAll($fetch_style);
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
    public function erron():string
    {
        if ($this->stmt) {
            return $this->stmt->errorCode();
        }
        return false;
    }
    public static function lastInsertId():int
    {
        return self::$pdo->lastInsertId();
    }
    protected function auto_prefix(string $query)
    {
        return preg_replace('/#{(\S+?)}/', self::$prefix.'$1', $query);
    }
    protected function lazyQuery(string $query, array $array=[])
    {
        $query=self::auto_prefix($query);
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
            $database=Config::get('database.name', 'test');
            if (self::$pdo->query('USE '.$database)) {
                $this->database=$database;
            } else {
                _D()->warning(__('could not select database:%s, maybe you should create database.', $database), 0, E_ERROR, $debug[1]['file'], $debug[1]['line']);
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
                $tmp =$value;
                $value = $tmp[0];
                $type = $tmp[1];
            } else {
                $type=is_numeric($value)?PDO::PARAM_INT:PDO::PARAM_STR;
            }
            $stmt->bindValue($key, $value, $type);
        }

        $markstring='query>'.$stmt->queryString;
        _D()->time($markstring);
        $return=$stmt->execute();
        _D()->timeEnd($markstring);
        
        self::$queryCount++;
        if ($return) {
            if (Config::get('debug')) {
                _D()->debug($stmt->queryString, $this->values);
            }
        } else {
            _D()->warning($stmt->errorInfo()[2].':'.$stmt->queryString, $this->values);
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
                _D()->time('connect database');
                self::$pdo = new PDO($pdo, Config::get('database.user', 'root'), Config::get('database.passwd', 'root'));
                _D()->timeEnd('connect database');
            } catch (PDOException $e) {
                _D()->waring('connect database error:'.$e->getMessage());
                self::$good=false;
            }
        }
    }

    public function good() :bool
    {
        return self::$good;
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
        return self::$pdo->beginTransaction();
    }
    
    public static function commit()
    {
        self::connectPdo();
        return  self::$pdo->commit();
    }

    public static function rollBack()
    {
        self::connectPdo();
        return  self::$pdo->rollBack();
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
}
