<?php
namespace suda\orm;

use suda\orm\connection\Connection;

class DataSource
{
    /**
     * 读连接
     *
     * @var Connection[]
     */
    protected $write = [];

    /**
     * 写连接
     *
     * @var Connection[]
     */
    protected $read = [];

    /**
     * 当前写数据库
     *
     * @var Connection|null
     */
    protected $master;

    /**
     * 当前读数据库
     *
     * @var Connection|null
     */
    protected $slave;

    /**
     * 添加连接
     *
     * @param Connection $connection
     * @return self
     */
    public function add(Connection $connection)
    {
        $this->addRead($connection);
        $this->addWrite($connection);
        return $this;
    }

    /**
     * 添加读连接
     *
     * @param Connection $connection
     * @return self
     */
    public function addRead(Connection $connection)
    {
        if (!\in_array($connection, $this->read)) {
            $this->read[] = $connection;
        }
        return $this;
    }


    /**
     * 添加写连接
     *
     * @param Connection $connection
     * @return self
     */
    public function addWrite(Connection $connection)
    {
        if (!\in_array($connection, $this->write)) {
            $this->write[] = $connection;
        }
        return $this;
    }
    
    /**
     * 获取写连接
     *
     * @return Connection
     */
    public function read():Connection
    {
        $this->selectReadConnection();
        return $this->slave;
    }

    /**
     * 获取读连接
     *
     * @return Connection
     */
    public function write():Connection
    {
        $this->selectWriteConnection();
        return $this->master;
    }

    /**
     * 读数据库选择
     *
     * @return void
     */
    protected function selectReadConnection()
    {
        $postion = mt_rand(0, count($this->read) -1);
        $this->slave = $this->read[$postion];
    }

    /**
     * 写数据库选择
     *
     * @return void
     */
    protected function selectWriteConnection()
    {
        if ($this->master === null) {
            $postion = mt_rand(0, count($this->write) -1);
            $this->master = $this->write[$postion];
        }
    }
}
