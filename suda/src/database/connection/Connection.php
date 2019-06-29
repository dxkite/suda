<?php
namespace suda\database\connection;

use PDO;
use PDOException;
use ReflectionException;
use suda\database\DataSource;
use suda\database\statement\Statement;
use suda\database\statement\QueryAccess;
use suda\database\exception\SQLException;
use suda\database\connection\observer\Observer;
use suda\database\connection\observer\NullObserver;

/**
 * 数据表链接对象
 *
 */
abstract class Connection
{
    /**
     * @var string
     */
    protected $type = 'mysql';

    /**
     * Config
     *
     * @var array
     */
    protected $config;

    /**
     * @var int
     */
    protected $queryCount = 0;

    /**
     * @var PDO
     */
    protected $pdo;

    /**
     * @var int
     */
    protected $transaction = 0;

    /**
     * @var
     */
    protected $id;

    /**
     * @var int
     */
    protected static $connectionCount = 0;

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
     * @param string|null $name
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

    /**
     * @return mixed
     */
    abstract public function getDsn();

    /**
     * @return PDO
     */
    abstract public function createPDO(): PDO;

    /**
     * 连接服务器
     *
     * @return bool
     * @throws SQLException
     */
    public function connect()
    {
        // 链接数据库
        if (null === $this->pdo && $this->getConfig('enable', true)) {
            try {
                $this->pdo = $this->createPDO();
                $this->id = static::$connectionCount;
                static::$connectionCount ++;
            } catch (PDOException $e) {
                throw new SQLException(sprintf(
                    "%s connect database error:%s",
                    $this->getName(),
                    $e->getMessage()
                ), $e->getCode(), $e);
            }
        }
        return $this->isConnected();
    }

    /**
     * 获取PDO
     * @ignore-dump
     * @return PDO
     * @throws SQLException
     */
    public function getPdo()
    {
        if (!$this->connect()) {
            throw new SQLException(sprintf(
                "%s data source is not connected",
                $this->getName()
            ), SQLException::ERR_NO_CONNECTION);
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
    public function getConfig(string $name, $default = null)
    {
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
     * @throws SQLException
     */
    protected function onBeforeSystemShutdown()
    {
        if ($this->pdo && ($this->transaction > 0 || $this->pdo->inTransaction())) {
            throw new SQLException(sprintf(
                "SQL transaction is open (%d) in connection %s",
                $this->transaction,
                $this->__toString()
            ), SQLException::ERR_TRANSACTION);
        }
    }

    /**
     * 查询SQL
     *
     * @param Statement $statement
     * @return mixed
     * @throws SQLException
     */
    public function query(Statement $statement)
    {
        $source = new DataSource();
        $source->add($this);
        return (new QueryAccess($source))->run($statement);
    }

    /**
     * 转义字符
     *
     * @param $string
     * @return string
     * @throws SQLException
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
     * @throws SQLException
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

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @param string $name
     * @return mixed
     */
    abstract public function switchDatabase(string $name);

    /**
     * @param string $name
     * @return mixed
     */
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


    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }


    /**
     * 自动填充前缀
     *
     * @param string $query
     * @return string
     */
    public function prefix( string $query):string
    {
        // _:table 前缀控制
        $prefix = $this->getConfig('prefix', '');
        return preg_replace('/_:(\w+)/', $prefix.'$1', $query);
    }
}
