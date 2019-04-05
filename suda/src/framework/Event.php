<?php
namespace suda\framework;


use suda\framework\Config;
use suda\framework\runnable\Runnable;

class Event
{
    protected  $queue=[];

    /**
     * 加载事件处理
     *
     * @param array $arrays
     * @return void
     */
    public  function load(array $arrays)
    {
        $this->queue=array_merge_recursive($this->queue, $arrays);
    }

    /**
     * 注册一条命令
     *
     * @param string $name
     * @param mixed $command
     * @return void
     */
    public  function listen(string $name, $command)
    {
        $this->add($name, $command);
    }

    /**
     * 注册一条命令
     *
     * @param string $name
     * @param mixed $command
     * @return void
     */
    public  function register(string $name, $command)
    {
        $this->add($name, $command);
    }

    /**
     * 添加命令到底部
     *
     * @param string $name
     * @param mixed $command
     * @return void
     */
    public  function add(string $name, $command)
    {
        $this->queue[$name][]=$command;
    }

    /**
     * 添加命令到顶部
     *
     * @param string $name
     * @param mixed $command
     * @return void
     */
    public  function addTop(string $name, $command)
    {
        if (\array_key_exists($name, $this->queue)  && is_array($this->queue[$name])) {
            array_unshift($this->queue[$name], $command);
        } else {
            $this->add($name, $command);
        }
    }

    /**
     * 移除一条命令
     *
     * @param string $name
     * @param mixed $remove
     * @return void
     */
    public  function remove(string $name, $remove)
    {
        if (\array_key_exists($name, $this->queue)  && is_array($this->queue[$name])) {
            foreach ($this->queue[$name] as $key=>$command) {
                if ($command === $remove) {
                    unset($this->queue[$name][$key]);
                }
            }
        }
    }

    #===================================================================
    #       命令运行
    #===================================================================

    /**
     * 运行所有命令
     *
     * @param string $name
     * @param array $args
     * @return void
     */
    public  function exec(string $name, array $args=[])
    {
        if (\array_key_exists($name, $this->queue) && is_array($this->queue[$name])) {
            foreach ($this->queue[$name] as $command) {
                $this->call($command, $args);
            }
        }
    }

    /**
     * 运行最先注入的命令
     *
     * @param string $name
     * @param array $args
     * @return mixed|null
     */
    public  function execFirst(string $name, array $args=[])
    {
        if (\array_key_exists($name, $this->queue) && is_array($this->queue[$name])) {
            return  $this->call(array_shift($this->queue[$name]), $args);
        }
        return null;
    }

    /**
     * 运行最后一个注入的命令
     *
     * @param string $name
     * @param array $args
     * @return mixed|null
     */
    public  function execLast(string $name, array $args=[])
    {
        if (\array_key_exists($name, $this->queue) && is_array($this->queue[$name])) {
            return $this->call(array_pop($this->queue[$name]), $args);
        }
        return null;
    }

    /**
     * 调用对象
     *
     * @param mixed $command
     * @param array $args
     * @return mixed
     */
    protected  function call($command, array &$args)
    {
        return (new Runnable($command))->apply($args);
    }
}
