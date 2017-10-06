<?php
namespace suda\archive\creator;

class Field
{
    // AUTO_INCREMENT
    protected $auto;
    // COMMENT
    protected $comment;
    protected $key; //primary unique index
    // foreign key
    protected $foreign;
 
    protected $name;
    protected $type;
    protected $length;
    protected $default;
    
    protected $null; // nullable
    protected $attribute; // binary unsigned
    protected $collation;
    protected $tableName;
    
    const BINARY='BINARY';
    const UNSIGNED='UNSIGNED';
    
    const UNIQUE='UNIQUE';
    const PRIMARY='PRIMARY';
    const INDEX='INDEX';
    const KEY='KEY';

    public function __construct(string $tableName, string $name, string $type, int $length=null)
    {
        $this->tableName=$tableName;
        $this->name=$name;
        $this->type=strtoupper($type);
        $this->length=$length;
    }

    public function comment(string $comment){
        $this->comment=$comment;
        return $this;
    }

    public function length(int $length)
    {
        $this->length=$length;
        return $this;
    }
    
    public function key()
    {
        $this->key=self::KEY;
        return $this;
    }
    public function primary()
    {
        $this->key=self::PRIMARY;
        return $this;
    }
    public function index()
    {
        $this->key=self::INDEX;
        return $this;
    }
    public function unique()
    {
        $this->key=self::UNIQUE;
        return $this;
    }
    public function collaction(string $collation)
    {
        $this->collaction=$collation;
        return $this;
    }
    
    public function auto()
    {
        $this->auto=true;
        return $this;
    }

    public function foreign(Field $field)
    {
        $this->foreign=$field;
        return $this;
    }

    public function null(bool $set=true)
    {
        $this->null=$set;
        return $this;
    }

    public function default(string $value)
    {
        $this->default=$value;
        return $this;
    }

    public function binary()
    {
        $this->attribute=self::BINARY;
        return $this;
    }

    public function unsigned()
    {
        $this->attribute=self::UNSIGNED;
        return $this;
    }

    public function getFieldSQL()
    {
        $type= $this->length?strtoupper($this->type).'('.$this->length.')':strtoupper($this->type);
        $auto=$this->auto?'AUTO_INCREMENT':'';
        $null=$this->null?'NULL':'NOT NULL';
        $attr=$this->attribute?strtoupper($this->attribute):'';
        $comment=$this->comment?('COMMENT \''.addcslashes($this->comment, '\'').'\''):'';
        $default=$this->default?'DEFAULT \''.addcslashes($this->default, '\'').'\'':'';
        return trim("`{$this->name}` {$type} {$attr} {$null} {$default} {$auto} {$comment}");
    }

    public function getKeyType()
    {
        return $this->key;
    }

    public function getName()
    {
        return $this->name;
    }
    public function getTableName()
    {
        return $this->tableName;
    }

    public function getForeignKey()
    {
        return $this->foreign;
    }

    public function getAutoIncrement(){
        return $this->auto;
    }    
}
