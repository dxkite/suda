<?php
namespace suda\orm\connection;

use PDO;
use PDOException;
use PDOStatement;
use function register_shutdown_function;
use suda\orm\struct\Fields;
use suda\orm\statement\Statement;
use suda\orm\statement\QueryAccess;
use suda\orm\exception\SQLException;
use suda\orm\connection\observer\Observer;
use suda\orm\connection\observer\NullObserver;

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
     * 链接别名
     *
     * @var string
     */
    protected $name;

    /**
     * 性能观测
     *
     * @var Observer
     */
    protected $observer;
    
    /**
     * 创建连接
     *
     * @param array $config
     */
    public function __construct(array $config, string $name = null)
    {
        $this->config = $config;
        $this->name = $name ?? 'anonymous';
        $this->observer = new NullObserver;
        register_shutdown_function(function () {
            $this->onBeforeSystemShutdown();
        });
    }

    abstract public function getDsn();
    
    abstract public function createPDO(): PDO;

    /**
     * 连接服务器
     *
     * @return bool
     */
    public function connect()
    {
        // 链接数据库
        if (null === $this->pdo && $this->getConfig('enable', true)) {
            try {
                $this->pdo = $this->createPDO();
                $this->id = static::$_id;
                static::$_id ++;
            } catch (PDOException $e) {
                throw new SQLException($this->getName().'connect database error:'.$e->getMessage(), $e->getCode(), E_ERROR, __FILE__, __LINE__, $e);
            }
        }
        return $this->isConnected();
    }

    /**
     * 获取PDO
     * @ignore-dump
     * @return PDO
     */
    public function getPdo()
    {
        if (!$this->connect()) {
            throw new SQLException($this->getName().' data source is not connected', SQLException::ERR_NO_CONNECTION);
        }
        return $this->pdo;
    }

    /**
     * 获取配置
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getConfig(string $name, $default = null) {
        return $this->config[$name] ?? $default;
    }

    /**
     * 获取最后一次插入的主键ID（用于自增值
     *
     * @param string|null $name
     * @return string
     */
    public function lastInsertId(?string $name = null):string
    {
        if (null === $name) {
            return $this->pdo->lastInsertId();
        } else {
            return $this->pdo->lastInsertId($name);
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
        return $this->pdo !== null;
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
            throw new SQLException('SQL transaction is open (' . $this->transaction.') in connection '.$this->__toString(), SQLException::ERR_TRANSACTION);
        }
    }

    /**
     * 查询SQL
     *
     * @param Statement $statement
     * @return mixed
     */
    public function query(Statement $statement)
    {
        return (new QueryAccess($this))->run($statement);
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
        return $this->getName();
    }

    abstract public function switchDatabase(string $name);
    abstract public function rawTableName(string $name);

    /**
     * Get 链接别名
     *
     * @return  string
     */
    public function getName()
    {
        return 'DataConnection['.$this->name.'][at '.$this->getDsn().']';
    }

    /**
     * Get 性能观测
     *
     * @return  Observer
     */
    public function getObserver():Observer
    {
        return $this->observer;
    }

    /**
     * Set 性能观测
     *
     * @param  Observer  $observer  性能观测
     *
     * @return  self
     */
    public function setObserver(Observer $observer)
    {
        $this->observer = $observer;

        return $this;
    }
}
