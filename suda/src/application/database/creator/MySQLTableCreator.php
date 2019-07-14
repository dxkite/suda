<?php
namespace suda\application\database\creator;

use function implode;
use suda\database\exception\SQLException;
use suda\database\struct\Field;
use suda\database\struct\TableStruct;
use suda\database\connection\Connection;
use suda\database\statement\QueryStatement;

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
     * @var TableStruct
     */
    protected $fields;

    /**
     *
     */
    const ENGINE_MYISAM = 'MyISAM';

    /**
     *
     */
    const ENGINE_INNODB = 'InnoDB';

    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $engine = self::ENGINE_INNODB;
    /**
     * @var
     */
    protected $comment;

    /**
     * @var
     */
    protected $collate;
    /**
     * @var string
     */
    protected $charset = 'utf8mb4';

    /**
     * @var
     */
    protected $auto;

    /**
     * MySQLTableCreator constructor.
     * @param Connection $connection
     * @param TableStruct $fields
     */
    public function __construct(Connection $connection, TableStruct $fields)
    {
        $this->name = $fields->getName();
        $this->fields = $fields;
        $this->connection = $connection;
    }

    /**
     * @return bool
     * @throws SQLException
     */
    public function create()
    {
        $statement = new QueryStatement($this->toSQL());
        $statement->setType(QueryStatement::WRITE);
        return $this->connection->query($statement) > 0;
    }

    /**
     * @return string
     */
    public function toSQL()
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


    /**
     * @return string
     */
    protected function parsePrimaryKeys()
    {
        $primary = [];
        foreach ($this->fields->all() as $field) {
            if ($field->getType() === Field::PRIMARY) {
                $primary[] = '`'.$field->getName().'`';
            }
        }
        if (count($primary)) {
            return 'PRIMARY KEY ('. implode(',', $primary).')';
        }
        return  '';
    }


    /**
     * @param array $content
     * @return array
     */
    protected function parseUniqueKeys(array $content)
    {
        foreach ($this->fields->all() as $field) {
            if ($field->getType() === Field::UNIQUE) {
                $content[] = 'UNIQUE KEY `'.$field->getName().'` (`'.$field->getName().'`)';
            }
        }
        return $content;
    }


    /**
     * @param array $content
     * @return array
     */
    protected function parseIndexKeys(array $content)
    {
        foreach ($this->fields->all() as $field) {
            if ($field->getType() === Field::INDEX) {
                $content[] = 'INDEX (`'.$field->getName().'`)';
            }
        }
        return $content;
    }

    /**
     * @param array $content
     * @return array
     */
    protected function parseKeys(array $content)
    {
        foreach ($this->fields->all() as $field) {
            if ($field->getType() === Field::INDEX) {
                $content[] = 'KEY `'.$field->getName().'` (`'.$field->getName().'`)';
            }
        }
        return $content;
    }


    /**
     * @param array $content
     * @return array
     */
    protected function parseForeignKeys(array $content)
    {
        foreach ($this->fields->all() as $field) {
            if ($field->getForeignKey() !== null) {
                $content[] = sprintf(
                    "FOREIGN KEY (`%s`) REFERENCES  `_:%s` (`%s`)",
                    $field->getName(),
                    $field->getTableName(),
                    $field->getName()
                );
            }
        }
        return $content;
    }

    /**
     * @param $length
     * @return string
     */
    protected function parseLength($length)
    {
        if ($length !== null) {
            if (is_string($length) || is_int($length)) {
                return '('.$length.')';
            }
            if (is_array($length)) {
                return '('.implode(',', $length).')';
            }
        }
        return  '';
    }

    /**
     * @param Field $field
     * @return string
     */
    protected function createField(Field $field)
    {
        $type = strtoupper($field->getValueType()).$this->parseLength($field->getLength());
        $auto = $field->getAuto() ?'AUTO_INCREMENT':'';
        $null = $field->isNullable() ?'NULL':'NOT NULL';
        $attr = $field->getAttribute() ?strtoupper($field->getAttribute()):'';
        $comment = $field->getComment() ?('COMMENT \''.addcslashes($field->getComment(), '\'').'\''):'';
        // default设置
        if ($field->hasDefault()) {
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
