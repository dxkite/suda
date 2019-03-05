<?php
namespace suda\orm\connection;

use PDO;
use PDOException;
use suda\orm\connection\Connection;
use suda\orm\exception\SQLException;

/**
 * 数据表链接对象
 *
 */
class SQLiteConnection extends Connection
{
    public static $type = 'sqlite';

    public function getDsn()
    {
        if (!array_key_exists('path', $this->config)) {
            throw new SQLException('config missing host');
        }
        $path = $this->config['path'];
        return static::$type.':'.$path;
    }
    
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

    public function createIfNotExists(Fields $fields)
    {
        // $this->getPdo()->query('USE ' . $table);
    }

    public function switchTable(string $table)
    {
        $this->getPdo()->query('USE ' . $table);
    }
}
