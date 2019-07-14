<?php
namespace suda\framework\runnable;

use ReflectionException;
use suda\framework\runnable\target\FileTarget;
use suda\framework\runnable\target\MethodTarget;
use suda\framework\runnable\target\ClosureTarget;
use suda\framework\runnable\target\TargetBuilder;
use suda\framework\runnable\target\FunctionTarget;

/**
 * 可执行命令表达式
 *
 */
class Runnable
{

    /**
     * 运行对象
     *
     * @var FunctionTarget|MethodTarget|FileTarget|ClosureTarget|Runnable
     */
    protected $target;

    public function __construct($runnable, array $parameter = [])
    {
        if ($runnable instanceof self) {
            $this->target = $runnable->target;
        } else {
            $this->target = TargetBuilder::build($runnable, $parameter);
        }
    }

    /**
     * Get 运行对象
     *
     * @return  FunctionTarget|MethodTarget|FileTarget|ClosureTarget
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * 获取名字
     *
     * @return string
     */
    public function getName()
    {
        return $this->target->getName();
    }

    /**
     * 是否可执行
     *
     * @return boolean
     */
    public function isValid():bool
    {
        return $this->target->isValid();
    }

    /**
     * 判断是否为闭包
     *
     * @return boolean
     */
    public function isClosure():bool
    {
        return $this->target instanceof ClosureTarget;
    }

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
     * 执行代码
     *
     * @param array $parameter
     * @return mixed
     */
    public function apply(array $parameter)
    {
        return $this->target->apply($parameter);
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

    /**
     * @param string $class
     * @return object
     * @throws ReflectionException
     */
    public static function newClassInstance(string $class)
    {
        return TargetBuilder::newClassInstance($class);
    }
}
