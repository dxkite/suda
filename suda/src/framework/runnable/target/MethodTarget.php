<?php

namespace suda\framework\runnable\target;

use function class_exists;
use function get_class;
use function is_object;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use function str_replace;

/**
 * 可执行命令：类方法
 *
 */
class MethodTarget extends FileTarget
{
    /**
     * 构建方法参数
     *
     * @var array|null
     */
    private $constructParameter;

    /**
     * 类对象
     *
     * @var object|string
     */
    private $object;

    /**
     * 类方法
     *
     * @var string
     */
    private $method;

    /**
     * 可执行对象
     *
     * @var array|null
     */
    protected $runnableTarget;

    /**
     * 构造对象
     *
     * @param string|object $object
     * @param array|null $constructParameter
     * @param string $method
     * @param array $parameter
     */
    public function __construct($object, ?array $constructParameter, string $method, array $parameter = [])
    {
        parent::__construct(null, $parameter);
        $this->object = is_object($object) ? $object : $this->getPHPClassName($object);
        $this->constructParameter = $constructParameter;
        $this->method = $method;
        $static = $this->isStatic() ? '->' : '::';
        $name = is_object($object) ? get_class($object) : $object;
        $this->name = $name . $static . $method;
    }

    /**
     * 获取对象实例
     *
     * @return object|null 动态类可获取
     * @throws ReflectionException
     */
    public function getObjectInstance(): ?object
    {
        if (is_object($this->object)) {
            return $this->object;
        }
        if (null === $this->constructParameter) {
            return null;
        }
        if (null !== $this->requireFile && !class_exists($this->object)) {
            require_once $this->requireFile;
        }
        $classRef = new ReflectionClass($this->object);
        return $classRef->newInstanceArgs($this->constructParameter);
    }

    /**
     * @return array|mixed|null
     * @throws ReflectionException
     */
    public function getRunnableTarget()
    {
        if (null === $this->runnableTarget) {
            if ($this->isStatic() || is_object($this->object)) {
                $this->runnableTarget = [$this->object, $this->method];
            }
            $this->runnableTarget = [$this->getObjectInstance() ?? $this->object, $this->method];
        }
        return $this->runnableTarget;
    }

    /**
     * 判断是否为静态方法
     *
     * @return boolean
     */
    public function isStatic(): bool
    {
        return !is_object($this->object) && null === $this->constructParameter;
    }

    /**
     * Set 需要的文件
     *
     * @param string|null $requireFile 需要的文件
     *
     * @return  self
     */
    public function setRequireFile($requireFile)
    {
        if (null === $this->requireFile) {
            $this->requireFile = $requireFile;
            $this->name = $this->name . '@' . $requireFile;
        }
        return $this;
    }

    /**
     * 是否可执行
     *
     * @return boolean
     */
    public function isValid(): bool
    {
        return is_object($this->object) || class_exists($this->object) || parent::isValid();
    }

    /**
     * 执行代码
     *
     * @param array $parameter
     * @return mixed
     * @throws ReflectionException
     */
    public function apply(array $parameter)
    {
        $runnable = $this->getRunnableTarget();
        $method = new ReflectionMethod($runnable[0], $runnable[1]);
        if (!$method->isPublic()) {
            $method->setAccessible(true);
        }
        if (count($parameter) == 0) {
            $parameter = $this->getParameter();
        }
        if (is_object($runnable[0])) {
            return $method->invokeArgs($runnable[0], $parameter);
        } else {
            return $method->invokeArgs(null, $parameter);
        }
    }

    /**
     * Get 类方法
     *
     * @return  string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Get 构建方法参数
     *
     * @return  array
     */
    public function getConstructParameter()
    {
        return $this->constructParameter;
    }

    /**
     * 获取PHP类名
     *
     * @param string $className
     * @return string
     */
    protected function getPHPClassName(string $className): string
    {
        return str_replace('.', '\\', $className);
    }
}
