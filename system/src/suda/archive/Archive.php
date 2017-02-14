<?php
namespace archive;

abstract class Archive extends \base\Value
{
    protected $want=[];
    
    public function setWants($name)
    {
        if (func_num_args() === 1){
            $name=func_get_args()[0];
        }
        else {
            $name=func_get_args();
        }
        $this->want=$name;
        return $this;
    }

    public function getWants():array
    {
        return $this->want;
    }
    
    /**
     * @param string $name
     * @param $value
     * @return mixed
     */
    public function __set(string $name, $value)
    {
        if ($this->_isField($name))
        {
            $this->var[$name]=$value;
        }
        else{
            throw new \Exception("Unknown Field $name From Table {$this->getTableName()}");
        }
    }
    // 是否为可用字段
    abstract protected function _isField($name);
    abstract public function getTableName();
}
