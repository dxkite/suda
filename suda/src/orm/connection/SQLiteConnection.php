<?php
namespace suda\orm\connection;

use PDO;
use PDOException;
use ReflectionException;
use suda\orm\exception\SQLException;
use suda\orm\statement\QueryStatement;

/**
 * 数据表链接对象
 *
 */
class SQLiteConnection extends Connection
{
    /**
     * @var string
     */
    protected $type = 'sqlite';

    /**
     * @return mixed|string
     * @throws SQLException
     */
    public function getDsn()
    {
        if (!array_key_exists('path', $this->config)) {
            throw new SQLException('config missing path', SQLException::ERR_CONFIGURATION);
        }
        $path = $this->config['path'];
        return $this->type.':'.$path;
    }

    /**
     * @return PDO
     * @throws SQLException
     */
    public function createPDO(): PDO
    {
        try {
            $pdo = new PDO($this->getDsn());
            $this->id = static::$connectionCount;
            static::$connectionCount ++;
            return $pdo;
        } catch (PDOException $e) {
            throw new SQLException(
                sprintf("%s connect database error:%s", $this->__toString(), $e->getMessage()),
                $e->getCode(),
                E_ERROR,
                __FILE__,
                __LINE__,
                $e
            );
        }
    }

    /**
     * @param string $database
     * @return mixed
     * @throws SQLException
     * @throws ReflectionException
     */
    public function switchDatabase(string $database)
    {
        return $this->query(new QueryStatement('USE `' . $database.'`'));
    }

    /**
     * @param string $name
     * @return mixed|string
     */
    public function rawTableName(string $name)
    {
        $prefix = $this->config['prefix'] ?? '';
        return $prefix.$name;
    }
}
