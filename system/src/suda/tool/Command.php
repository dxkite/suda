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
namespace suda\tool;

use suda\core\Runnable;
use suda\exception\CommandException;

/**
 * 可执行命令表达式
 *
 */
class Command
{
    /**
     * 可执行对象
     *
     * @var Runnable
     */
    protected $runnable;

    public function __construct($command, array $parameter=[])
    {
        $this->runnable = new Runnable($command, $parameter);
    }
    
    public function name(string $name)
    {
        $this->runnable->setName($name);
        return $this;
    }

    public function params(array $parameter)
    {
        $this->runnable->setParameter($parameter);
        return $this;
    }

    public function exec(array $parameter=[])
    {
        return Runnable::invoke($this->runnable, $parameter);
    }

    public function args(...$args)
    {
        return $this->exec($args);
    }
    
    /**
       * 绝对调用函数，可调用类私有和保护函数
       *
       * @param mixed $command
       * @param mixed $params
       * @return mixed
       */
    public static function invoke($command, $parameter)
    {
        return Runnable::invoke($command, $parameter);
    }

    /**
     * 创建类对象
     *
     * @param string $class
     * @return object
     */
    public static function newClassInstance(string $class)
    {
        return Runnable::newClassInstance($class);
    }

    public function __toString()
    {
        return $this->runnable->getName();
    }
}
