<?php
namespace suda\orm\connection\creator;

use PDO;
use PDOException;
use suda\orm\struct\Field;
use suda\orm\struct\Fields;
use suda\orm\statement\Statement;
use suda\orm\connection\Connection;
use suda\orm\exception\SQLException;
use suda\orm\statement\QueryStatement;

/**
 * 数据表链接对象
 *
 */
class MySQLTableCreator
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

    public function __construct(Connection $connection, Fields $fields)
    {
        $this->name = $fields->getName();
        $this->fields = $fields;
        $this->connection = $connection;
    }

    public function create()
    {
        $statement = new QueryStatement($this->toSQL());
        $statement->isWrite(true);
        return $this->connection->query($statement) > 0;
    }

    protected function toSQL()
    {
        $content = [];
        foreach ($this->fields->all() as $field) {
            $content[] = $this->createField($field);
        }
        $content[] = $this->parsePrimaryKeys();
        $content = $this->parseUniqueKeys($content);
        $content = $this->parseIndexKeys($content);
        $content = $this->parseKeys($content);
        $content = $this->parseForeignKeys($content);
        $table = $this->connection->rawTableName($this->name);
        $sql = "CREATE TABLE IF NOT EXISTS `{$table}` (\r\n\t";
        $sql .= implode(",\r\n\t", array_filter($content, 'strlen'));
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
        foreach ($this->fields->all() as $field) {
            if ($field->getForeignKey() !== null) {
                $content[] = 'FOREIGN KEY (`'.$field->getName().'`) REFERENCES  `#{'.$field->getTableName().'}` (`'.$field->getName().'`)';
            }
        }
        return $content;
    }

    protected function createField(Field $field)
    {
        $type = $field->getLength()? strtoupper($field->getValueType()).'('.$field->getLength().')':strtoupper($field->getValueType());
        $auto = $field->getAuto() ?'AUTO_INCREMENT':'';
        $null = $field->getNull() ?'NULL':'NOT NULL';
        $attr = $field->getAttribute() ?strtoupper($field->getAttribute()):'';
        $comment = $field->getComment() ?('COMMENT \''.addcslashes($field->getComment(), '\'').'\''):'';
        // default设置
        if ($field->isDefault()) {
            if (null === $field->getDefault()) {
                $default = 'DEFAULT NULL';
            } else {
                $default = 'DEFAULT \''.addcslashes($field->getDefault(), '\'').'\'';
            }
        } else {
            $default = '';
        }
        $list = ['`'.$field->getName().'`', $type, $attr, $field->getCharset(), $null, $default, $auto, $comment];
        return implode(' ', array_filter(array_map('trim', $list), 'strlen'));
    }
}
