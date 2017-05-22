<?php
namespace  suda\archive;

use Iterator;
use JsonSerializable;
use suda\exception\ArchiveException;

class Archive implements Iterator,JsonSerializable
{
    /**
     * @var
     */
    protected $fields;
    protected $key;
    protected $fieldKeys;
        
        
    /**
     * 设置表列
     *
     * @param array $fields
     * @return void
     */
    public function _setFieldKeys(array $fields)
    {
        $this->fieldKeys=$fields;
        return $this;
    }

    /**
     * 获取全部的列
     *
     * @return array
     */
    public function _getFieldKeys():array
    {
        return $this->fieldKeys;
    }
    /**
     * @return mixed
     */
    public function _setFieldValues(array $fields)
    {
        $this->fields=$fields;
        return true;
    }

    /**
     * @param mixed $var
     */
    public function _getFieldValues()
    {
        return $this->fields;
    }

    /**
     * 自动set get 函数适配
     * @param string $name
     * @param $args
     * @return mixed|string
     */
    public function __call(string $methodName, $args)
    {
        $debug=debug_backtrace();
        $file=$debug[0]['file'];
        $line=$debug[0]['line'];
        if (preg_match('/^(set|get)(.+)$/', $methodName, $match)) {
            array_shift($match);
            list($method, $name)=$match;
            $name=$this->nameAlias(lcfirst($name));
            if (in_array($name, array_values($this->_getFieldKeys()))) {
                if ($method==='get') {
                    return $this->fields ?? null;
                } else {
                    if (count($args)>0) {
                        $this->fields[$name]=$args[0];
                        return $this;
                    } else {
                        throw new ArchiveException(__('miss method %s args#1', $methodName), ArchiveException::MISS_ARGUMENT ,E_ERROR,$file,$line);
                    }
                }
            } else {
                throw new ArchiveException(__('unknow field %s', $name), ArchiveException::UNKOWN_FIELDNAME , E_ERROR,$file,$line);
            }
        } else {
            throw new ArchiveException(__('unknow method %s', $name),  ArchiveException::UNKOWN_METHOD  , E_ERROR,$file,$line);
        }
    }

    /// 迭代器扩展
    public function rewind()
    {
        reset($this->fields);
        $this->key=key($this->fields);
    }

    public function current()
    {
        return  current($this->fields);
    }

    public function key()
    {
        return $this->key=key($this->fields);
    }

    public function next()
    {
        next($this->fields);
    }

    public function valid()
    {
        return isset($this->fields[$this->key]);
    }
    public function jsonSerialize()
    {
        return $this->fields;
    }
    public function __toString()
    {
        return json_encode($this);
    }
    
    protected function nameAlias(string $name)
    {
        return strtolower(preg_replace('/([A-Z])/', '_$1', $name));
    }
}
