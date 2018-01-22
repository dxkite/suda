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
 * @version    since 1.2.4
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
    public $name;

    public function __construct($command, array $params=[])
    {
        $this->command= is_string($command)?self::parseCommand($command):$command;
        $this->params=$params;
        $this->name=is_string($command)?$command:'{closure command}';
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
        debug()->trace(__('exec command %s with args %s', $this->name, json_encode($params)));
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
        // 非空调用
        if ($this->command) {
            // 是函数调用&指定了文件&函数不存在
            if (is_string($this->command) && !function_exists($this->command) && $this->file) {
                require_once $this->file;
            }
            // 调用接口
            elseif (is_array($this->command)) {
                if (!is_object($this->command[0])) {
                    if ($this->static) {
                    } else {
                        $this->command[0]=new $this->command[0];
                    }
                }
            }
            
            if (!is_callable($this->command)) {
                throw (new CommandException(__('command {%s} is uncallable', $this->name)))->setCmd($this->name)->setParams($this->params);
            }
            return static::_absoluteCall($this->command, $this->params);
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
    
    private function parseCommand(string $command)
    {
        if (preg_match('/^(?:([\w\\\\\/.]+))?(?:(#|->|::)(\w+))?(?:\((.+?)\))?(?:@(.+))?$/', $command, $matchs)) {
            debug()->trace(__('parse command %s', $command), $matchs);
            // $this->name=$command;
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
            throw (new CommandException('unknown:'.$command))->setCmd($command);
        }
    }

    /**
     * 绝对调用函数，可调用类私有和保护函数
     *
     * @param [type] $command
     * @param [type] $params
     * @return void
     */
    public static function _absoluteCall($command, $params)
    {
        if (is_array($command)) {
            $method = new \ReflectionMethod($command[0], $command[1]);
            if ($method->isPrivate() || $method->isProtected()) {
                $method->setAccessible(true);
            }
            if (is_object($command[0])) {
                return $method->invokeArgs($command[0], $params);
            } else {
                return $method->invokeArgs(null, $params);
            }
        } else {
            return forward_static_call_array($command, $params);
        }
    }
}
