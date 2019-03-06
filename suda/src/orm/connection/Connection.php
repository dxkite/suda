<?php
namespace suda\orm\connection;

use PDO;
use PDOException;
use suda\orm\struct\Fields;
use suda\orm\exception\SQLException;

/**
 * 数据表链接对象
 *
 */
abstract class Connection
{
    public static $type = 'mysql';

    /**
     * Config
     *
     * @var array
     */
    protected $config;

    protected $queryCount = 0;
    protected $pdo = null;
    protected $transaction = 0;
    protected $id;
    protected static $_id = 0;
    protected static $defaultConnection = null;
    
    /**
     * 创建连接
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        \register_shutdown_function(function () {
            $this->onBeforeSystemShutdown();
        });
    }

    abstract public function getDsn();
    
    abstract public function createPDO(): PDO;

    /**
     * 连接服务器
     *
     * @return self
     */
    public function connect()
    {
        // 链接数据库
        if (null === $this->pdo && ($this->config['enable'] ?? true)) {
            try {
                $this->pdo = $this->createPDO();
                $this->id = static::$_id;
                static::$_id ++;
            } catch (PDOException $e) {
                throw new SQLException($this->__toString().' connect database error:'.$e->getMessage(), $e->getCode(), E_ERROR, __FILE__, __LINE__, $e);
            }
        }
        return $this;
    }

    public function getPdo()
    {
        $this->connect();
        return $this->pdo;
    }

    /**
     * 获取最后一次插入的主键ID（用于自增值
     *
     * @param string $name
     * @return null|int
     */
    public function lastInsertId(string $name = null):?int
    {
        if (null === $name) {
            return $this->pdo->lastInsertId()?:null;
        } else {
            return $this->pdo->lastInsertId($name)?:null;
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
        $this->transaction ++;
        if ($this->transaction == 1) {
            $this->pdo->beginTransaction();
        }
    }

    /**
     * 判断是否连接
     *
     * @return boolean
     */
    public function isConnected():bool
    {
        return $this->pdo != null;
    }

    /**
     * 事务系列，提交事务
     *
     * @return void
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
     * @return void
     */
    public function rollBack()
    {
        if ($this->transaction == 1) {
            $this->transaction = 0;
            $this->pdo->rollBack();
        } else {
            $this->transaction--;
        }
    }

    /**
     * 事务关闭检测
     *
     * @return void
     */
    protected function onBeforeSystemShutdown()
    {
        if ($this->pdo && ($this->transaction > 0 || $this->pdo->inTransaction())) {
            throw new SQLException('SQL transaction is open (' . $this->transaction.') in connection '.$this->__toString());
        }
    }

    /**
     * 查询SQL
     *
     * @param string $sql
     * @return void
     */
    public function query(string $sql)
    {
        if ($stmt = $this->getPdo()->query($sql)) {
            return $stmt ->execute();
        }
        $debug=debug_backtrace();
        throw (new SQLException($this->getPdo()->errorInfo()[2], intval($this->getPdo()->errorCode()), E_ERROR, $debug[1]['file'], $debug[1]['line']))->setSql($sql);
    }

    /**
     * 转义字符
     *
     * @param array $array
     * @return string
     */
    public function quote($string)
    {
        return $this->getPdo()->quote($string);
    }

    /**
     * 转义字符
     *
     * @param array $array
     * @return string
     */
    public function arrayQuote(array $array)
    {
        $temp = array();
        foreach ($array as $value) {
            $temp[] = is_int($value) ? $value : $this->quote($value);
        }
        return implode(',', $temp);
    }

    /**
     * 统计查询数量
     *
     * @return void
     */
    public function countQuery()
    {
        $this->queryCount++;
    }

    public function __toString()
    {
        return 'DB Connection ['.$this->type.'] {'.$this->getDsn().'}';
    }

    abstract public function createTable(Fields $fields);
    abstract public function switchDatabase(string $name);
    abstract public function rawTableName(string $name);
}
