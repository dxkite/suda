<?php
namespace suda\framework\runnable\target;

use Closure;

/**
 * 可执行命令：文件类型
 *
 */
class ClosureTarget extends RunnableTarget
{

    /**
     * 对象
     *
     * @var Closure
     */
    protected $closure;
    
    public function __construct(Closure $closure, array $parameter = [])
    {
        $this->closure = $closure;
        $this->parameter = $parameter;
        $this->name = 'Closure object()';
    }

    public function getRunnableTarget()
    {
        return $this->closure;
    }


    /**
     * 是否可执行
     *
     * @return boolean
     */
    public function isValid():bool
    {
        return true;
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
        return forward_static_call_array($this->closure, $parameter);
    }
}
