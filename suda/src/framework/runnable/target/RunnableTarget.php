<?php
namespace suda\framework\runnable\target;

/**
 * 可执行命令表目标
 *
 */
abstract class RunnableTarget
{
    
    /**
     * 参数
     *
     * @var array
     */
    protected $parameter;
    
    /**
     * 名称
     *
     * @var string
     */
    protected $name;

    /**
     * Get 可执行对象
     *
     * @return  mixed
     */
    abstract public function getRunnableTarget();
    /**
     * 是否可执行
     *
     * @return boolean
     */
    abstract public function isValid():bool;
    /**
     * 执行代码
     *
     * @param array $parameter
     * @return mixed
     */
    abstract public function apply(array $parameter);

    /**
     * 执行代码
     *
     * @param mixed ...$args
     * @return mixed
     */
    public function run(...$args)
    {
        return $this->apply($args);
    }

    /**
     * Get 执行参数
     *
     * @return  array
     */
    public function getParameter()
    {
        return $this->parameter;
    }

    /**
     * Set 执行参数
     *
     * @param  array  $parameter  执行参数
     *
     * @return  self
     */
    public function setParameter(array $parameter)
    {
        $this->parameter = $parameter;

        return $this;
    }

    /**
     * Get 可执行命令字符串表示
     *
     * @return  string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set 可执行命令字符串表示
     *
     * @param string $name 可执行命令字符串表示
     *
     * @return  self
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * 调用函数
     *
     * @param mixed ...$args
     * @return mixed
     */
    public function __invoke(...$args)
    {
        return $this->apply($args);
    }
}
