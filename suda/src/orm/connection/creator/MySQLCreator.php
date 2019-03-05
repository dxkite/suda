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
class MySQLCreator
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
        $this->connection->getPdo()->query($sql);
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
        if (!is_array($this->fields)) {
            return false;
        }
        $content = [];
        foreach ($this->fields as $field) {
            $content[] = $field->getFieldSQL();
        }
        $content[] = $this->parsePrimaryKeys();
        $content = $this->parseUniqueKeys($content);
        $content = $this->parseIndexKeys($content);
        $content = $this->parseKeys($content);
        $content = $this->parseForeignKeys($content);
        $table = $this->connection->rawTableName($this->name);
        $sql = "CREATE TABLE `{$table}` (\r\n\t";
        $sql .= implode(",\r\n\t", $content);
        $auto = null === $this->auto?'':'AUTO_INCREMENT='.$this->auto;
        $collate = null === $this->collate?'':'COLLATE '.$this->collate;
        $sql .= "\r\n) ENGINE={$this->engine} {$collate} {$auto} DEFAULT CHARSET={$this->charset};";
        return $sql;
    }


    protected function parsePrimaryKeys()
    {
        $primary = [];
        foreach ($this->fields->all() as  $field) {
            if ($field->getType() === Field::PRIMARY) {
                $primary[] = '`'.$field->getName().'`';
            }
        }
        if (count($primary)) {
            $primary = 'PRIMARY KEY (';
            foreach ($this->primaryKeys as $field) {
                $primary .= '`'.$field->getName().'`,';
            }
            return 'PRIMARY KEY ('.\implode(',', $primary).')';
        }
    }


    protected function parseUniqueKeys(array $content)
    {
        foreach ($this->fields->all() as  $field) {
            if ($field->getType() === Field::UNIQUE) {
                $content[] = 'UNIQUE KEY `'.$field->getName().'` (`'.$field->getName().'`)';
            }
        }
        return $content;
    }


    protected function parseIndexKeys(array $content)
    {
        foreach ($this->fields->all() as  $field) {
            if ($field->getType() === Field::INDEX) {
                $content[] = 'INDEX (`'.$field->getName().'`)';
            }
        }
        return $content;
    }

    protected function parseKeys(array $content)
    {
        foreach ($this->fields->all() as  $field) {
            if ($field->getType() === Field::INDEX) {
                $content[] = 'KEY `'.$field->getName().'` (`'.$field->getName().'`)';
            }
        }
        return $content;
    }


    protected function parseForeignKeys(array $content)
    {
        if (is_array($this->foreignKeys)) {
            foreach ($this->foreignKeys as $name => $field) {
                $content[] = 'FOREIGN KEY (`'.$name.'`) REFERENCES  `#{'.$field->getTableName().'}` (`'.$field->getName().'`)';
            }
        }
        return $content;
    }
}
