<?php
namespace suda\archive\creator;

class Table
{
    const ENGINE_MyISAM='MyISAM';
    const ENGINE_InnoDB='InnoDB';

    protected $fields;
    protected $name;
    protected $engine=self::ENGINE_InnoDB;
    protected $comment;
    protected $collate;
    protected $charset='utf8';
    protected $auto;
    protected $indexKeys;
    protected $foreignKeys;
    protected $primaryKeys;
    protected $uniqueKeys;
    protected $keys;

    public function __construct(string $name, string $charset='utf8')
    {
        $this->name=$name;
        $this->charset=$charset;
    }

    /**
     * 新建表列
     *
     * @param string $name
     * @param string $type
     * @param int $length
     * @return void
     */
    public function field(string $name, string $type, int $length=null)
    {
        return $this->fields[$name]??$this->fields[$name]=($length?new Field($this->name, $name, $type, $length):new Field($this->name, $name, $type));
    }

    public function newField(string $name, string $type, int $length=null)
    {
        return $this->fields[$name]??$this->fields[$name]=($length?new Field($this->name, $name, $type, $length):new Field($this->name, $name, $type));
    }

    /**
     * 表全部的列
     *
     * @param [type] $fields
     * @return void
     */
    public function fields($fields)
    {
        if (!is_array($fields) && $fields instanceof Field) {
            $fields=func_get_args();
        }
        foreach ($fields as $field) {
            $this->addField($field);
        }
        return $this;
    }

    public function addField(Field $field)
    {
        $name=$field->getName();
        $this->fields[$name]=$field;

        if ($key=$field->getKeyType()) {
            switch ($key) {
                case $field::INDEX:
                    $this->indexKeys[$name]=$field;
                    break;
                case $field::PRIMARY:
                    $this->primaryKeys[$name]=$field;
                    break;
                case $field::UNIQUE:
                    $this->uniqueKeys[$name]=$field;
                    break;
                case $field::KEY:
                    $this->keys[$name]=$field;
                    break;
            }
        }
        if ($foreign=$field->getForeignKey()) {
            $this->foreignKeys[$name]=$foreign;
        }
        return $this;
    }

    public function getSQL()
    {
        if (!is_array($this->fields)) {
            return false;
        }
        $content=[];
        foreach ($this->fields as $field) {
            $content[]=$field->getFieldSQL();
        }
        if (is_array($this->primaryKeys)) {
            $primary='PRIMARY KEY (';
            foreach ($this->primaryKeys as $field) {
                $primary .= '`'.$field->getName().'`,';
            }
            $content[]=trim($primary, ',').')';
        }
        if (is_array($this->uniqueKeys)) {
            foreach ($this->uniqueKeys as $field) {
                $content[]='UNIQUE KEY `'.$field->getName().'` ('.$field->getName().')';
            }
        }
        if (is_array($this->indexKeys)) {
            foreach ($this->indexKeys as $field) {
                $content[]='INDEX ('.$field->getName().')';
            }
        }
        if (is_array($this->keys)) {
            foreach ($this->keys as $field) {
                $content[]='KEY `'.$field->getName().'` ('.$field->getName().')';
            }
        }
        if (is_array($this->foreignKeys)) {
            foreach ($this->foreignKeys as $name=>$field) {
                $content[]='FOREIGN KEY (`'.$name.'`) REFERENCES  `'.$field->getTableName().'` (`'.$field->getName().'`)';
            }
        }
        
        $sql="CREATE TABLE `#{{$this->name}}` (\r\n\t";
        $sql.=implode(",\r\n\t", $content);
        $auto=is_null($this->auto)?'':'AUTO_INCREMENT='.$this->auto;
        $collate=is_null($this->collate)?'':'COLLATE '.$this->collate;
        $sql.="\r\n) ENGINE={$this->engine} {$collate} {$auto} DEFAULT CHARSET={$this->charset};";
        return $sql;
    }

    public function setAuto(int $auto)
    {
        $this->auto=$auto;
        return $this;
    }
    
    public function setCharset(int $charset)
    {
        $this->charset=$charset;
        return $this;
    }
    
    public function setEngine(int $engine)
    {
        $this->engine=$engine;
        return $this;
    }

    public function setComment(int $comment)
    {
        $this->comment=$comment;
        return $this;
    }

    public function setCollate(int $collate)
    {
        $this->collate=$collate;
        return $this;
    }

    public function getFieldsName()
    {
        return array_keys($this->fields);
    }

    public function getPrimaryKeysName()
    {
        return array_keys($this->primaryKeys);
    }
}
