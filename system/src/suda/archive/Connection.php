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
 * @version    since 1.2.14
 */

namespace suda\archive;

use PDO;
use PDOException;
use suda\exception\SQLException;
use suda\core\Config;

/**
 * 数据表链接对象
 *
 */
class Connection
{
    public $type = 'mysql';
    public $host = '127.0.0.1';
    public $port = 3306;
    public $charset ='utf8';
    public $prefix ='dx_';
    public $user='root';
    public $password='';
    public $database ='';
    public $id =0;
    public $name = 'default';
    protected $queryCount=0;
    protected $pdo=null;
    protected $transaction = 0;
    protected static $_id=0;
 

    public function __toString()
    {
        return 'DB Connection ['.$this->name.'] {'.$this->getDsn().'}';
    }

    public static function getDefaultConnection()
    {
        return self::getConnection();
    }

    public static function getConnection(?string $name=null)
    {
        $connection = is_null($name) ? 'database':'database.connections.'.$name;
        $conn = new self();
        $conn->type= 'mysql';
        if (!is_null($name)) {
            $conn->name = $name;
        }
        $conn->host= Config::get($connection.'.host', 'localhost');
        $conn->database= Config::get($connection.'.name', 'suda_framework');
        $conn->charset=Config::get($connection.'.charset', 'utf8');
        $conn->port=Config::get($connection.'.port', 3306);
        $conn->user = Config::get($connection.'.user', 'root');
        $conn->password = Config::get($connection.'.passwd', '');
        return $conn;
    }

    protected function getDsn()
    {
        return $this->type.':host='.$this->host.';charset='.$this->charset.';port='.$this->port;
    }
    
    public function connect()
    {
        // 链接数据库
        if (is_null($this->pdo) && conf('enableQuery', true)) {
            $this->prefix=Config::get('database.prefix', '');
            try {
                debug()->time('connect database');
                hook()->exec('SQL:connectPdo::before');
                $this->pdo = new PDO($this->getDsn(), $this->user, $this->password);
                debug()->timeEnd('connect database');
                $this->id =static::$_id;
                static::$_id ++;
                hook()->listen('system:shutdown::before', [$this,'onBeforeSystemShutdown']);
                debug()->info('connected ('.$this->id.') '.$this->__toString());
            } catch (PDOException $e) {
                throw new SQLException($this->__toString().' connect database error:'.$e->getMessage(), $e->getCode(), E_ERROR, __FILE__, __LINE__, $e);
            }
        }
        return $this;
    }

    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * 获取最后一次插入的主键ID（用于自增值
     *
     * @param string $name
     * @return false|int false则获取失败，整数则获取成功
     */
    public function lastInsertId(string $name=null)
    {
        if (is_null($name)) {
            return $this->pdo->lastInsertId();
        } else {
            return $this->pdo->lastInsertId($name);
        }
        return false;
    }

    /**
     * 事务系列，开启事务
     *
     * @return any
     */
    public function begin()
    {
        return self::beginTransaction();
    }

    /**
     * 事务系列，开启事务
     *
     * @return any
     */
    public function beginTransaction()
    {
        $this->transaction ++;
        if ($this->transaction == 1) {
            $this->pdo->beginTransaction();
        }
    }

    public function isConnected()
    {
        return $this->pdo != null;
    }

    /**
     * 事务系列，提交事务
     *
     * @return any
     */
    public function commit()
    {
        if ($this->transaction == 1) {
            $this->pdo->commit();
        }
        $this->transaction--;
    }

    /**
     * 事务系列，撤销事务
     *
     * @return any
     */
    public function rollBack()
    {
        if ($this->transaction == 1) {
            $this->transaction=0;
            $this->pdo->rollBack();
        } else {
            $this->transaction--;
        }
    }


    protected function onBeforeSystemShutdown()
    {
        if ($this->transaction > 0 || $this->pdo->inTransaction()) {
            debug()->error('SQL transaction is open (' . $transaction.') in connection '.$this->__toString());
        }
    }

    public function quote($string)
    {
        return $this->pdo->quote($string);
    }

    public function arrayQuote(array $array)
    {
        $temp = array();
        foreach ($array as $value) {
            $temp[] = is_int($value) ? $value : $this->pdo->quote($value);
        }
        return implode($temp, ',');
    }

    public function prefixStr(string $query)
    {
        return preg_replace('/#{(\S+?)}/', $this->prefix.'$1', $query);
    }

    public function countQuery()
    {
        $this->queryCount++;
    }
}
