<?php
namespace suda\orm\connection;

use PDO;
use PDOException;
use suda\orm\struct\Fields;
use suda\orm\connection\Connection;
use suda\orm\exception\SQLException;
use suda\orm\connection\creator\MySQLCreator;

/**
 * 数据表链接对象
 *
 */
class MySQLConnection extends Connection
{
    public static $type = 'mysql';

    public function getDsn()
    {
        if (!array_key_exists('host', $this->config)) {
            throw new SQLException('config missing host');
        }
        
        $host = $this->config['host'];
        $charset = $this->config['charset'] ?? 'utf8mb4';
        $port = $this->config['charset'] ?? 3306;
        if (array_key_exists('name', $this->config)) {
            return static::$type.':host='.$host.';dbname='.$this->config['name'].';charset='.$charset.';port='.$port;
        }
        return static::$type.':host='.$host.';charset='.$charset.';port='.$port;
    }
    
    public function createPDO(): PDO
    {
        try {
            $user = $this->config['user'] ?? 'root';
            $password = $this->config['password'] ?? '';
            $pdo = new PDO($this->getDsn(), $user, $password);
            $this->id = static::$_id;
            static::$_id ++;
            return $pdo;
        } catch (PDOException $e) {
            throw new SQLException($this->__toString().' connect database error:'.$e->getMessage(), $e->getCode(), E_ERROR, __FILE__, __LINE__, $e);
        }
    }

    public function createTable(Fields $fields)
    {
        $creator = new MySQLCreator($this, $fields);
        return $creator->create();
    }

    public function switchDatabase(string $database)
    {
        return $this->query('USE `' . $database.'`');
    }

    public function rawTableName(string $name)
    {
        $prefix = $this->config['prefix'] ?? '';
        return $prefix.$name;
    }
}
