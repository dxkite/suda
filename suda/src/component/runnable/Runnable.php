<?php
namespace suda\component\runnable;

use suda\component\runnable\target\FileTarget;
use suda\component\runnable\target\MethodTarget;
use suda\component\runnable\target\ClosureTarget;
use suda\component\runnable\target\TargetBuilder;
use suda\component\runnable\target\FunctionTarget;
use suda\component\runnable\target\RunnableTarget;

/**
 * 可执行命令表达式
 *
 */
class Runnable   {

    /**
     * 运行对象
     *
     * @var FunctionTarget|MethodTarget|FileTarget|ClosureTarget
     */
    protected $target;

    public function __construct($runnable, array $parameter=[]) {
        $this->target = TargetBuilder::build($runnable, $parameter);
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
        return  $this->target->isValid();
    }

    /**
     * 执行代码
     *
     * @param mixed ...$args
     * @return mixed
     */
    public function run(...$args) {
        return $this->apply($args);
    }

    /**
     * 执行代码
     *
     * @param array $parameter
     * @return mixed
     */
    public function apply(array $parameter) {
        return $this->target->apply($parameter);
    }

    public static function newClassInstance(string $class)
    {
        return TargetBuilder::newClassInstance($class);
    }
}