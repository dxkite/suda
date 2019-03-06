<?php
namespace suda\orm\connection\creator;

use PDO;
use PDOException;
use suda\orm\struct\Field;
use suda\orm\struct\Fields;
use suda\orm\connection\Connection;
use suda\orm\exception\SQLException;

/**
 * 数据表链接对象
 *
 */
class SQLiteCreator
{
    /**
     * 连接
     *
     * @var Connection
     */
    protected $connection;

    /**
     * 字段
     *
     * @var Fields
     */
    protected $fields;

    const ENGINE_MyISAM = 'MyISAM';
    const ENGINE_InnoDB = 'InnoDB';

    protected $name;
    protected $engine = self::ENGINE_InnoDB;
    protected $comment;

    protected $collate;
    protected $charset = 'utf8';
    
    protected $auto;
    protected $foreignKeys;
    public function __construct(Connection $connection, Fields $fields)
    {
        $this->name = $fields->getName();
        $this->fields = $fields;
        $this->connection = $connection;
    }

    public function create()
    {
        $sql = $this->toSQL();
        return $this->connection->query($sql);
    }

    protected function seekField(Field $field)
    {
        $name = $field->getName();
        $this->fields[$name] = $field;
        if ($foreign = $field->getForeignKey()) {
            $this->foreignKeys[$name] = $foreign;
        }
        return $this;
    }


    protected function toSQL()
    {
        if ($this->fields === null) {
            return null;
        }
        // TODO
        return $sql;
    }


}
