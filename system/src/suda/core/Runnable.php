<?php
/**
 * Suda FrameWork
 *
 * An open source application development framework for PHP 7.2.0 or newer
 *
 * Copyright (c)  2017-2018 DXkite
 *
 * @category   PHP FrameWork
 * @package    Suda
 * @copyright  Copyright (c) DXkite
 * @license    MIT
 * @link       https://github.com/DXkite/suda
 * @version    since 1.2.4
 */
namespace suda\core;

use ReflectionClass;
use suda\core\Autoloader;

/**
 * 可执行命令表达式
 *
 */
class Runnable
{
    /**
     * 可执行表达式
     *
     * @var mixed
     */
    protected $runnable=null;
    /**
     * 可执行对象
     *
     * @var mixed
     */
    protected $runnableTarget=null;
    /**
     * 需要的文件
     *
     * @var string|null
     */
    protected $requireFile=null;
    /**
     * 执行参数
     *
     * @var array
     */
    protected $parameter = [];

    /**
     * 可执行命令字符串表示
     *
     * @var string
     */
    protected $name;
    
    /**
     * 静态方法
     *
     * @var boolean
     */
    private $isStatic = false;
    

    /**
     * 创建可执行对象
     *
     * @param mixed $command
     * @param array $parameter
     */
    public function __construct($command, array $parameter=[])
    {
        if (\is_string($command)) {
            $this->parseCommand($command);
            $this->name = $command;
        } elseif ($command instanceof \Closure) {
            $this->name = 'Closure Object()';
            $this->runnableTarget = $command;
        } else {
            $this->runnableTarget = $command;
            $this->name = $this->arrayName($command);
        }
        $this->parameter  = \count($parameter) > 0 ?$parameter:$this->parameter;
    }

    /**
     * 获取可运行目标
     *
     * @return mixed
     */
    public function getRunnableTarget()
    {
        if (\is_null($this->runnableTarget)) {
            if (\is_array($this->runnable)) {
                if ($this->isStatic) {
                    $this->runnableTarget = $this->runnable;
                } else {
                    $this->runnableTarget = [
                        is_object($this->runnable[0])?$this->runnable[0]:static::newClassInstance($this->runnable[0]),
                        $this->runnable[1],
                    ];
                }
            } else {
                $this->runnableTarget = $this->runnable;
            }
        }
        return $this->runnableTarget;
    }

    /**
     * 是否可执行
     *
     * @return boolean
     */
    public function isInvaild():bool
    {
        return is_null($this->getRequireFile()) && is_null($this->getRunnableTarget());
    }

    /**
     * 获取依赖文件
     *
     * @return string|null
     */
    public function getRequireFile():?string
    {
        return $this->requireFile;
    }
    
    /**
     * 获取可运行表达式
     *
     * @return mixed|null
     */
    public function getRunnable()
    {
        return $this->runnable;
    }

    public function getParameter():array
    {
        return $this->parameter;
    }

    public function getName():string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name=$name;
        return $this;
    }

    public function setParameter(array $parameter)
    {
        $this->parameter=$parameter;
        return $this;
    }
    
    private static function splitParameter(string $command):array
    {
        $parameter = null;
        if (strrpos($command, ')') === strlen($command) -1) {
            $paramStart = strpos($command, '(');
            $parameter = substr($command, $paramStart + 1, strlen($command) - $paramStart - 2);
            $command = substr($command, 0, $paramStart);
        }
        return [$command,$parameter];
    }

    private static function buildParameter(?string $parameter)
    {
        if (is_null($parameter)) {
            return [];
        }
        return self::parseParameter($parameter);
    }

    private function buildCommandName(string $name)
    {
        if (preg_match('/^[\w\\\\\/.]+$/', $name) !== 1) {
            throw new \Exception(__('invaild command name: $0', $name));
        }
        return Autoloader::realName($name);
    }
    
    protected static function parseParameter(string $param)
    {
        $param = trim($param);
        if (strpos($param,'=') === 0) {
            list($prefix, $value) = explode(':', $param, 2);
            if (strpos($value,':') === 0) {
                $value = base64_decode(substr($value, 1));
            }
            if ($prefix ==='=j' || $prefix ==='=json') {
                $params = json_decode($value);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $params;
                }
                throw new \Exception(__('can not parse parameter $0', $param));
            } else {
                $params = unserialize($value);
                if (is_object($params)) {
                    return [$params];
                }
                return $params;
            }
        } else {
            $params = explode(',', trim($param, ','));
            foreach ($params as $index=>$value) {
                $params[$index]=trim($value);
            }
            return $params;
        }
    }

    /**
     * 解析表达式，参数只支持基本参数（数字，字符串）
     *
     * @param string $command
     * @return void
     */
    private function parseCommand(string $command)
    {
        $fileStart = \strrpos($command, '@');
        // for @file
        if ($fileStart > 0) {
            $this->requireFile = substr($command, $fileStart+1);
            $command = substr($command, 0, $fileStart);
        }
        if ($fileStart === 0) {
            return;
        }
        // for parameter list
        list($command, $parameter) = self::splitParameter($command);
        // for method
        $dynmicsMethod = strpos($command, '->');
        $splitLength = strpos($command, '#');
        $methodStart = $splitLength ?: strpos($command, '::') ?: $dynmicsMethod;
        $this->isStatic = !$dynmicsMethod;
        $this->parameter = self::buildParameter($parameter);
        if ($methodStart > 0) {
            $splitLength = $splitLength > 0 ? 1:2;
            $methodName = substr($command, $methodStart + $splitLength);
            $command = substr($command, 0, $methodStart);
            $this->runnable = [$this->buildCommandName($command), $methodName];
        } else {
            $this->runnable = $this->buildCommandName($command);
        }
    }

    /**
     * 创建类对象
     *
     * @param string $class
     * @return object
     */
    public static function newClassInstance(string $class)
    {
        list($className, $parameter) = self::splitParameter($class);
        $classRelName = Autoloader::realName($className);
        if (is_null($parameter)) {
            return new  $classRelName;
        }
        $parameters=self::buildParameter($parameter);
        $classRef= new ReflectionClass($classRelName);
        return $classRef->newInstanceArgs($parameters);
    }

    
    /**
     * 绝对调用函数，可调用类私有和保护函数
     *
     * @param mixed $runnable
     * @param mixed $parameter
     * @return mixed
     */
    public static function invoke($runnable, $parameter)
    {
        if ($runnable instanceof Runnable) {
            return static::invokeRunnable($runnable, $parameter);
        } elseif (is_array($runnable)) {
            $method = new \ReflectionMethod($runnable[0], $runnable[1]);
            if ($method->isPrivate() || $method->isProtected()) {
                $method->setAccessible(true);
            }
            if (is_object($runnable[0])) {
                return $method->invokeArgs($runnable[0], $parameter);
            } else {
                return $method->invokeArgs(null, $parameter);
            }
        } else {
            return forward_static_call_array($runnable, $parameter);
        }
    }

    protected static function invokeRunnable(Runnable $runnable,array $parameter) {
        if ($runnable->isInvaild()) {
            throw new \Exception(__('invaild runnable: $0', $runnable->getName()));
        }
        // 集合所有参数
        if (count($parameter) == 0) {
            $parameter = $runnable->getParameter();
        }
        debug()->trace(__('exec runnable $0 with args', $runnable->getName()), $parameter);
        $runnableTarget = $runnable->getRunnableTarget();
        $requireFile = $runnable->getRequireFile();
        // 文件引入
        if (is_null($runnableTarget)) {
            // 文件参数引入
            array_unshift($params, $requireFile);
            $_SERVER['argv']=$parameter;
            $_SERVER['args']=count($parameter);
            return include $requireFile;
        } else {
            if (!is_null($requireFile)) {
                require_once $requireFile;
            }
            return static::invoke($runnableTarget, $parameter);
        }
    } 
    
    public function __toString()
    {
        return $this->getName();
    }

    private function arrayName(array $command):string
    {
        return \is_string($command[0]) ? $command[0].'::'. $command[1] : get_class($command[0]) .'->'.$command[1];
    }
}
