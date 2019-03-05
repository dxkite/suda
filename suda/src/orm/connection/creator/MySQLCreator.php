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
    protected $indexKeys;
    protected $foreignKeys;
    protected $primaryKeys;
    protected $uniqueKeys;
    protected $keys;

    public function __construct(Connection $connection, Fields $fields)
    {
        $this->name = $fields->getName();
        $this->fields = $fields;
    }

    public function create()
    {
        foreach ($this->fields as $field) {
            $this->seekField($field);
        }
        $sql = $this->toSQL();
        $this->connection->getPdo()->query($sql);
    }

    protected function seekField(Field $field)
    {
        $name = $field->getName();
        $this->fields[$name] = $field;
        if ($key = $field->getKeyType()) {
            switch ($key) {
                case $field::INDEX:
                    $this->indexKeys[$name] = $field;
                    break;
                case $field::PRIMARY:
                    $this->primaryKeys[$name] = $field;
                    break;
                case $field::UNIQUE:
                    $this->uniqueKeys[$name] = $field;
                    break;
                case $field::KEY:
                    $this->keys[$name] = $field;
                    break;
            }
        }
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
        $content[] = $this->parseUniqueKeys();
        $content[] = $this->parseIndexKeys();
        $content[] = $this->parseKeys();
        $content[] = $this->parseForeignKeys();
        $sql = "CREATE TABLE `#{{$this->name}}` (\r\n\t";
        $sql .= implode(",\r\n\t", $content);
        $auto = null === $this->auto?'':'AUTO_INCREMENT='.$this->auto;
        $collate = null === $this->collate?'':'COLLATE '.$this->collate;
        $sql .= "\r\n) ENGINE={$this->engine} {$collate} {$auto} DEFAULT CHARSET={$this->charset};";
        return $sql;
    }


    protected function parsePrimaryKeys()
    {
        if (is_array($this->primaryKeys)) {
            $primary = 'PRIMARY KEY (';
            foreach ($this->primaryKeys as $field) {
                $primary .= '`'.$field->getName().'`,';
            }
            return trim($primary, ',').')';
        }
    }


    protected function parseUniqueKeys()
    {
        if (is_array($this->uniqueKeys)) {
            foreach ($this->uniqueKeys as $field) {
                $content[] = 'UNIQUE KEY `'.$field->getName().'` (`'.$field->getName().'`)';
            }
        }
    }


    protected function parseIndexKeys()
    {
        if (is_array($this->indexKeys)) {
            foreach ($this->indexKeys as $field) {
                $content[] = 'INDEX (`'.$field->getName().'`)';
            }
        }
    }

    protected function parseKeys()
    {
        if (is_array($this->keys)) {
            foreach ($this->keys as $field) {
                $content[] = 'KEY `'.$field->getName().'` (`'.$field->getName().'`)';
            }
        }

    }


    protected function parseForeignKeys()
    {
        if (is_array($this->foreignKeys)) {
            foreach ($this->foreignKeys as $name => $field) {
                $content[] = 'FOREIGN KEY (`'.$name.'`) REFERENCES  `#{'.$field->getTableName().'}` (`'.$field->getName().'`)';
            }
        }
    }
}
