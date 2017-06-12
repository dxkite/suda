<?php
/**
 * Suda FrameWork
 *
 * An open source application development framework for PHP 7.0.0 or newer
 * 
 * Copyright (c)  2017 DXkite
 *
 * @category   PHP FrameWork
 * @package    Suda
 * @copyright  Copyright (c) DXkite
 * @license    MIT
 * @link       https://github.com/DXkite/suda
 * @version    1.2.4
 */
namespace suda\archive;

use suda\core\Storage;
use suda\tool\Value;

// 数据表对象文件读取器
class DTOReader
{
    protected $fields;
    protected $sets;
    protected $auto;
    protected $namespace;
    protected $name;
    protected $file;
    protected $tableName;
    protected $exset=[];

    protected $unique=[];
    protected $primary=[];
    protected $keys=[];

    public function export(string $template, string $path)
    {
        ob_start();
        
        $_SQL=new Value([
            'fields'=>$this->fields,
            'sets'=>$this->sets,
            'name'=>$this->name,
            'namespace'=>$this->namespace,
            'keys'=>$this->keys,
            'unique'=>$this->unique,
            'primary'=>$this->primary,
        ]);

        require $template;
        $class=ob_get_clean();
        file_put_contents($path, "<?php\r\n".$class."\r\n\r\n/**\r\n* DTA FILE:\r\n".$this->file."\r\n*/");
        return $path;
    }

    public function getFieldsStr(string $key=null)
    {
        $fields=$this->fields;
        if ($key && isset($fields[$key])){
            unset($fields[$key]);
        }
        return '[\''.implode('\',\'', array_keys($fields)).'\']';
    }
    public function updataParams(){
        $str=[];
     
        foreach ($this->fields as $name =>$type){
            $type=preg_match('/int/',$type)?'int':'string'; 
            if(!isset($this->primary[$name])){
                $str[]=$type.' $'.$name.'=null';
            }
        }
        return implode(',',$str);
    }

    
    public function load(string $path)
    {
        if (file_exists($path)) {
            $this->file=file_get_contents($path);
            $file=file($path);
            foreach ($file as $line) {
                // 空白 注释 字段名 附加属性 注释
                if (preg_match('/^(?:\s*)(?!;)(\w+)\s+(\S+)(?:\s+(.+?))?(;(.*))?$/', $line, $match)) {
                    $this->fields[$match[1]]=$match[2];
                    $this->sets[$match[1]]=self::parser_str($match[3]);
                    $name=$match[1];
                    $type=$match[2];
                    if (isset($this->sets[$name]['auto'])) {
                        $this->auto=$name;
                    } 
                    if (isset($this->sets[$name]['primary'])) {
                        $this->primary[$name]=$type;
                    } 
                    if (isset($this->sets[$name]['unique'])) {
                        $this->unique[$name]=$type;
                        $this->keys[$name]=$type;
                    } 
                    if (isset($this->sets[$name]['key'])) {
                       $this->keys[$name]=$type;
                    }
                } elseif (preg_match('/^#\s*(.+)\s*$/', $line, $exset)) {
                    $this->exset=array_merge($this->exset, self::parser_str($exset[1]));
                }
            }
        }
    }

    protected static function parser_str(string $sets)
    {
        $values=[];
        preg_match_all('/(\w+)(?:=(\'|")?(\S+)(?(2)\2))?\s*/', $sets, $matchs);
        for ($i=0;$i<count($matchs[0]);$i++) {
            $name=$matchs[1][$i];
            $str=strcmp($matchs[2][$i], '"') && strcmp($matchs[2][$i], '\'');
            $value=$matchs[3][$i];
            if (preg_match('/^(true|false)$/i', $matchs[3][$i])) {
                $value=$matchs[3][$i]==='true';
            } elseif (is_numeric($matchs[3][$i])) {
                settype($value, 'integer');
            }
            $values[$name]=$value;
        }
        return $values;
    }

    public function getCreateSQL():string
    {
        $create=[];
        $sets=[];
        foreach ($this->fields as $name => $type) {
            $type=strtoupper($type);
            $auto=isset($this->sets[$name]['auto'])?'AUTO_INCREMENT':'';
            $null=isset($this->sets[$name]['null'])?'NULL':'NOT NULL';
            $unsigned=isset($this->sets[$name]['unsigned'])?'UNSIGNED':'';
            $comment=isset($this->sets[$name]['comment'])?('COMMENT \''.$this->sets[$name]['comment'].'\''):'';
            $default=isset($this->sets[$name]['default'])?'DEFAULT \''.addcslashes($this->sets[$name]['default'], '\'').'\'':'';
            $create[]=trim("`{$name}` {$type} {$unsigned} {$null} {$default} {$auto} {$comment}");
            if (isset($this->sets[$name]['primary'])) {
                $sets[]="PRIMARY KEY (`{$name}`)";
            } elseif (isset($this->sets[$name]['unique'])) {
                $sets[]="UNIQUE KEY `{$name}` (`{$name}`)";
            } elseif (isset($this->sets[$name]['key'])) {
                $sets[]="KEY `{$name}` (`{$name}`)";
            }
        }
        $sql="CREATE TABLE `{$this->tableName}` (\r\n\t";
        $sql.=implode(",\r\n\t", array_merge($create, $sets));
        $auto=$this->auto?'AUTO_INCREMENT=0':'';
        $sql.="\r\n) ENGINE=InnoDB {$auto} DEFAULT CHARSET=utf8;";
        return $sql;
    }

    /**
     * @return mixed
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @return mixed
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @param mixed $tableName
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
    }

    /**
     * @return mixed
     */
    public function getSets()
    {
        return $this->sets;
    }

    /**
     * @return mixed
     */
    public function getAuto()
    {
        return $this->auto;
    }

    /**
     * @return string
     */
    public function getNamespace() : string
    {
        return $this->namespace;
    }

    /**
     * @param string $namespace
     */
    public function setNamespace(string $namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * @return mixed
     */
    public function getName():string
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }
    
    /**
     * @return mixed
     */
    public function getExset():array
    {
        return $this->exset;
    }
}
