<?php
namespace suda\framework\runnable\target;

use suda\framework\runnable\target\FileTarget;

/**
 * 可执行命令：函数名
 *
 */
class FunctionTarget extends FileTarget
{

    /**
     * 可执行函数名
     *
     * @var string
     */
    protected $function;
 
    public function __construct(string $name, array $parameter = [])
    {
        $this->setParameter($parameter);
        $this->function = $name;
    }
    
    public function getRunnableTarget()
    {
        return $this->function;
    }

    /**
     * Get 需要的文件
     *
     * @return  string|null
     */
    public function getRequireFile()
    {
        return $this->requireFile;
    }

    /**
     * Set 需要的文件
     *
     * @param  string|null  $requireFile  需要的文件
     *
     * @return  self
     */
    public function setRequireFile($requireFile)
    {
        $this->requireFile = $requireFile;
        $this->setName($this->function.'@'.$requireFile);
        return $this;
    }

    /**
     * 是否可执行
     *
     * @return boolean
     */
    public function isValid():bool
    {
        return  function_exists($this->function) || parent::isValid();
    }

    /**
     * 执行代码
     *
     * @param array $parameter
     * @return mixed
     */
    public function apply(array $parameter)
    {
        if (count($parameter) == 0) {
            $parameter = $this->getParameter();
        }
        if (null !== $this->requireFile && !function_exists($this->function)) {
            require_once $this->requireFile;
        }
        return forward_static_call_array($this->function, $parameter);
    }

    /**
     * Get 可执行函数名
     *
     * @return  string
     */
    public function getFunction()
    {
        return $this->function;
    }
}
