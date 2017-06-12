<?php
/**
 * Suda FrameWork
 *
 * An open source application development framework for PHP 7.0.0 or newer
 * 
 * Copyright (c)  2017 DXkite
 *
 * @category   PHP FrameWork
 * @package    Suda
 * @copyright  Copyright (c) DXkite
 * @license    MIT
 * @link       https://github.com/DXkite/suda
 * @version    1.2.3
 */
namespace suda\tool;

use suda\exception\CommandException;

class Command
{
    public $command;
    public $file;
    public $static=false;
    public $params=[];
    public $func_bind=[];
    public $name='command';

    public function __construct($command, array $params=[])
    {
        $this->command=$command;
        $this->params=$params;
    }
    public function name(string $name)
    {
        $this->name=$name;
        return $this;
    }
    public function params(array $params)
    {
        $this->params=$params;
        return $this;
    }

    public function exec(array $params=[])
    {
        _D()->trace(__('exec command %s with args %s', $this->name, json_encode($params)));
        if (is_string($this->command)) {
            $this->command= self::parseCommand($this->command);
        }
        // 集合所有参数
        if (count($params)) {
            $this->params=$params;
        }

        // 设置了参数绑定
        if (count($this->func_bind)>0) {
            $args=[];
            foreach ($this->func_bind as $index=>$bind) {
                $args[$index]= $this->params[$bind] ?? null;
            }
            $this->params=$args;
        }
        // spl_autoload_register([$this,'loadCommand']);
        // 非空调用
        if ($this->command) {
            // 是函数调用&指定了文件&函数不存在
            if (is_string($this->command) && !function_exists($this->command) && $this->file) {
                require_once $this->file;
            }
            // 调用接口
            elseif (is_array($this->command) /*&& !is_callable($this->command)*/) {
                if ($this->static  || is_object($this->command[0])) {
                } else {
                    $this->command[0]=new $this->command[0];
                }
            }
            if (!is_callable($this->command)) {
                throw (new CommandException(__('command{%s} is uncallable',$this->name)))->setCmd($this->name)->setParams($this->params);
            }
            if ($this->static) {
                return forward_static_call_array($this->command, $this->params);
            } else {
                return call_user_func_array($this->command, $this->params);
            }
        } elseif ($this->file) {
            // 文件参数引入
            $params=array_unshift($params, $this->file);
            $_SERVER['argv']=$params;
            $_SERVER['args']=count($params);
            return include $this->file;
        }
        return false;
    }

    public function args()
    {
        return self::exec(func_get_args());
    }
    
    protected function parseCommand(string $command)
    {
        if (preg_match('/^(?:([\w\\\\\/.]+))?(?:(#|->|::)(\w+))?(?:\((.+?)\))?(?:@(.+))?$/', $command, $matchs)) {
            _D()->trace(__('parse command %s', $command));
            $this->name=$command;
            // 添加参数绑定
            if (isset($matchs[4])) {
                $this->func_bind=explode(',', trim($matchs[4], ','));
            }
            // 指定文件
            if (isset($matchs[5]) && $matchs[5]) {
                $this->file=$matchs[5];
            }
            // 调用方式
            if (isset($matchs[2])) {
                $this->static=(strcmp($matchs[2], '#')===0 || strcmp($matchs[2], '::')===0);
            }
            $matchs[1]=preg_replace('/[.\/]+/', '\\', $matchs[1]);
            // 方法名
            if (isset($matchs[3]) && $matchs[3]) {
                return [$matchs[1],$matchs[3]];
                // 函数名
            } elseif (isset($matchs[1]) && $matchs[1]) {
                return $matchs[1];
            }
        } else {
            throw (new CommandException('unknow:'.$command))->setCmd($command);
        }
    }
}
