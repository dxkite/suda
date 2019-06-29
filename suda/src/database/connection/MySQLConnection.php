<?php
namespace suda\database\connection;

use PDO;
use PDOException;
use ReflectionException;
use suda\database\statement\QueryStatement;
use suda\database\exception\SQLException;

/**
 * 数据表链接对象
 *
 */
class MySQLConnection extends Connection
{
    protected $type = 'mysql';

    /**
     * @return mixed|string
     * @throws SQLException
     */
    public function getDsn()
    {
        if (!array_key_exists('host', $this->config)) {
            throw new SQLException('config missing host');
        }
        
        $host = $this->config['host'];
        $charset = $this->config['charset'] ?? 'utf8mb4';
        $port = $this->config['port'] ?? 3306;
        if (array_key_exists('name', $this->config)) {
            return $this->type.':host='.$host.';dbname='.$this->config['name'].';charset='.$charset.';port='.$port;
        }
        return $this->type.':host='.$host.';charset='.$charset.';port='.$port;
    }

    /**
     * @return PDO
     * @throws SQLException
     */
    public function createPDO(): PDO
    {
        try {
            $user = $this->config['user'] ?? 'root';
            $password = $this->config['password'] ?? '';
            $pdo = new PDO($this->getDsn(), $user, $password);
            $this->id = static::$connectionCount;
            static::$connectionCount ++;
            return $pdo;
        } catch (PDOException $e) {
            throw new SQLException(sprintf(
                "%s connect database error:%s",
                $this->__toString(),
                $e->getMessage()
            ), $e->getCode(), $e);
        }
    }

    /**
     * @param string $database
     * @return mixed
     * @throws SQLException
     */
    public function switchDatabase(string $database)
    {
        return $this->query(new QueryStatement('USE `' . $database.'`'));
    }

    public function rawTableName(string $name)
    {
        $prefix = $this->config['prefix'] ?? '';
        return $prefix.$name;
    }
}
