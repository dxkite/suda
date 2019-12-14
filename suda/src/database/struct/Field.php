<?php
namespace suda\database\struct;

/**
 * 数据表字段创建工具
 * 用于创建数据表字段
 */
class Field
{
    /**
     * 自增 AUTO_INCREMENT
     *
     * @var bool
     */
    protected $auto;
    // COMMENT
    /**
     * @var
     */
    protected $comment;
    /**
     * @var
     */
    protected $key; //primary unique index
    // foreign key
    /**
     * @var
     */
    protected $foreign;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var int|string|array|null
     */
    protected $length;

    /**
     * @var mixed
     */
    protected $default;

    /**
     * @var bool
     */
    protected $hasDefault;

    /**
     * @var bool|null
     */
    protected $null; // isNullable

    /**
     * @var
     */
    protected $attribute; // binary unsigned

    /**
     * @var
     */
    protected $collation;
    /**
     * @var string
     */
    protected $tableName;
    /**
     * @var
     */
    protected $charset;
    /**
     * @var string
     */
    protected $alias;

    /**
     *
     */
    const BINARY = 'BINARY';
    /**
     *
     */
    const UNSIGNED = 'UNSIGNED';

    /**
     *
     */
    const UNIQUE = 'UNIQUE';
    /**
     *
     */
    const PRIMARY = 'PRIMARY';
    /**
     *
     */
    const INDEX = 'INDEX';
    /**
     *
     */
    const KEY = 'KEY';

    /**
     * Field constructor.
     * @param string $tableName
     * @param string $name
     * @param string $type
     * @param int|string|array|null $length
     */
    public function __construct(string $tableName, string $name, string $type, $length = null)
    {
        $this->tableName = $tableName;
        $this->name = $name;
        $this->type = strtoupper($type);
        $this->length = $length;
        $this->hasDefault = false;
        $this->alias = $name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function alias(string $name)
    {
        $this->alias = $name;
        return $this;
    }

    /**
     * @param string $charset
     * @return $this
     */
    public function charset(string $charset)
    {
        $this->charset = 'CHARACTER SET ' . $charset;
        return $this;
    }

    /**
     * @param string $comment
     * @return $this
     */
    public function comment(string $comment)
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @param $length
     * @return $this
     */
    public function length($length)
    {
        $this->length = $length;
        return $this;
    }

    /**
     * @return $this
     */
    public function key()
    {
        $this->key = self::KEY;
        return $this;
    }

    /**
     * @return $this
     */
    public function primary()
    {
        $this->key = self::PRIMARY;
        return $this;
    }

    /**
     * @return $this
     */
    public function index()
    {
        $this->key = self::INDEX;
        return $this;
    }

    /**
     * @return $this
     */
    public function unique()
    {
        $this->key = self::UNIQUE;
        return $this;
    }

    /**
     * @param string $collate
     * @return $this
     */
    public function collate(string $collate)
    {
        $this->collation = $collate;
        return $this;
    }

    /**
     * @return $this
     */
    public function auto()
    {
        $this->auto = true;
        return $this;
    }

    /**
     * @param Field $field
     * @return $this
     */
    public function foreign(Field $field)
    {
        $this->foreign = $field;
        $this->type = $field->type;
        $this->length = $field->length;
        $this->default = null;
        if ($field->attribute) {
            $this->attribute = $field->attribute;
        }
        return $this;
    }

    /**
     * @param bool $set
     * @return $this
     */
    public function null(bool $set = true)
    {
        $this->null = $set;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function default($value)
    {
        $this->hasDefault = true;
        $this->default = $value;
        if (null === $value) {
            $this->null = true;
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function binary()
    {
        $this->attribute = self::BINARY;
        return $this;
    }

    /**
     * @return $this
     */
    public function unsigned()
    {
        $this->attribute = self::UNSIGNED;
        return $this;
    }


    /**
     * Get the value of auto
     */
    public function getAuto()
    {
        return $this->auto;
    }

    /**
     * Get the value of comment
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Get the value of key
     */
    public function getType()
    {
        return $this->key;
    }

    /**
     * Get the value of foreign
     */
    public function getForeignKey()
    {
        return $this->foreign;
    }

    /**
     * Get the value of name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the value of type
     */
    public function getValueType()
    {
        return $this->type;
    }

    /**
     * Get the value of length
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * Get the value of default
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * Get the value of hasDefault
     */
    public function hasDefault()
    {
        return $this->hasDefault;
    }

    /**
     * 是否为主键
     *
     * @return boolean
     */
    public function isPrimary():bool
    {
        return  $this->key == self::PRIMARY;
    }

    /**
     * 是否为可空
     *
     * @return boolean
     */
    public function isNullable(): ?bool
    {
        return $this->null;
    }

    /**
     * 是否支持
     *
     * @return boolean
     */
    public function isAutoIncrement(): bool
    {
        return $this->auto === true;
    }
    
    /**
     * Get the value of attribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Get the value of collation
     */
    public function getCollation()
    {
        return $this->collation;
    }

    /**
     * Get the value of tableName
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Get the value of charset
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * Get the value of alias
     */
    public function getAlias()
    {
        return $this->alias ?? $this->name;
    }

    /**
     * @param Field $field
     * @return bool
     */
    public function equals(Field $field) {
        if ($this->name !== $field->name) {
            return false;
        }
        if ($this->type !== $field->type) {
            return false;
        }
        if ($this->length !== $field->length) {
            return false;
        }
        return true;
    }
}
