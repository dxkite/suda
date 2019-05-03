<?php
namespace suda\orm\connection;

use PDO;
use PDOException;
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
    public static $type = 'sqlite';

    /**
     * @return mixed|string
     * @throws SQLException
     */
    public function getDsn()
    {
        if (!array_key_exists('path', $this->config)) {
            throw new SQLException('config missing host', SQLException::ERR_CONFIGURATION);
        }
        $path = $this->config['path'];
        return static::$type.':'.$path;
    }

    /**
     * @return PDO
     * @throws SQLException
     */
    public function createPDO(): PDO
    {
        try {
            $pdo = new PDO($this->getDsn());
            $this->id = static::$_id;
            static::$_id ++;
            return $pdo;
        } catch (PDOException $e) {
            throw new SQLException($this->__toString().' connect database error:'.$e->getMessage(), $e->getCode(), E_ERROR, __FILE__, __LINE__, $e);
        }
    }

    /**
     * @param string $database
     * @return mixed
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
