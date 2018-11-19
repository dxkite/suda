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

use suda\exception\CommandException;
use suda\core\Autoloader;

class Command
{
    public $command;
    public $file;
    public $static=false;
    public $params=[];
    public $funcParam=[];
    public $constructParam=[];
    public $name;
    public $cmdstr;

    public function __construct($command, array $params=[])
    {
        $this->command= is_string($command)?self::parseCommand($command):$command;
        $this->params=$params;
        $this->name=is_string($command)? $command : 'Closure';
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
        debug()->trace(__('exec command $0 with args $1', $this->name, json_encode($params)));
        // 集合所有参数
        if (count($params)) {
            $this->params=$params;
        }

        // 设置了参数绑定
        if (count($this->funcParam)>0) {
            $args=[];
            foreach ($this->funcParam as $index=>$bind) {
                $args[$index]= $this->params[$index] ?? $this->funcParam[$index] ?? null;
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
                        if ($this->constructParam) {
                            $class = new \ReflectionClass($this->command[0]);
                            $this->command[0]= $class->newInstanceArgs($this->constructParam);
                        } else {
                            $this->command[0]=new $this->command[0];
                        }
                    }
                }
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
    
    /**
     * 解析表达式，参数只支持基本参数（数字，字符串）
     *
     * @param string $command
     * @return mixed
     */
    private function parseCommand(string $command)
    {
        
        // 支持表达式：
        // 1. [name.space.|name\space\|name/space/]Class[(param)]->method[(params)]
        // 2. [name.space.|name\space\|name/space/]Class#method[(params)]
        // 3. [name.space.|name\space\|name/space/]Class::method[(params)]
        // 4. [name.space.|name\space\|name/space/]function[(param)][@filepath]
        // 5. @filepath

        if (preg_match('/^ ([\w\\\\\/.]+) (\( (?: (?>[^()]+) | (?2) )* \))? (\-\>|\:\:|\#) (\w+) (\( (?: (?>[^()]+) | (?5) )* \))? $/ux', $command, $matchs)) {
            if (count($matchs) === 6) {
                list($cmdstr, $className, $constructParams, $type, $method, $methodParam) = $matchs;
                if (preg_match('/\((.+)\)/', $constructParams, $tmp)) {
                    $constructParams=$tmp[1];
                }
                if (preg_match('/\((.+)\)/', $methodParam, $tmp)) {
                    $methodParam=$tmp[1];
                }
            } else {
                list($cmdstr, $className, $constructParams, $type, $method) = $matchs;
                if (preg_match('/\((.+)\)/', $constructParams, $tmp)) {
                    $constructParams=$tmp[1];
                }
                $methodParam = null;
            }
            $className = Autoloader::realName($className);
            // debug()->trace(__('parse command $0 as rule 1,2,3', $command), $matchs);
            $this->name = $className.'->'.$method;
            if ($constructParams) {
                $this->constructParam = self::parseParam($constructParams);
            }
            if ($methodParam) {
                $this->funcParam = self::parseParam($methodParam);
            }
            $this->static = $type === '#' || $type === '::';
            return [$className,$method];
        } elseif (preg_match('/^ ([\w\\\\\/.]+) (\( ( (?>[^()]+) | (?2) )* \))? (?:\@(.+))? $/ux', $command, $matchs)) {
            $matchCount = count($matchs);
            if ($matchCount == 2) {
                list($cmdstr, $functionName) = $matchs;
                $functionParam = null;
                $functionFile = null;
            } elseif ($matchCount == 3) {
                list($cmdstr, $functionName, $functionParam) = $matchs;
                $functionFile = null;
            } else {
                list($cmdstr, $functionName, $functionParam, $functionFile) = $matchs;
            }
            $functionName = Autoloader::realName($functionName);
            if ($functionFile) {
                $this->file=$functionFile;
            }
            return $functionName;
        } elseif (preg_match('/\@(.+)$/', $command, $matchs)) {
            list($cmdstr, $functionFile) = $matchs;
            $this->file=$functionFile;
        } else {
            throw (new CommandException(__('unknown command: $0',$command)))->setCmd($command);
        }
        $this->cmdStr= $command;
    }

    protected static function parseParam(string $param)
    {
        $param = trim($param);
        if (preg_match('/^\=j(son)?\:(\:)?(.+)$/', $param, $matchs)) {
            if (isset($matchs[2]) && $matchs[2]) {
                $params = json_decode(base64_decode($matchs[3]));
            } else {
                $params = json_decode($matchs[3]);
            }
            if (json_last_error() === JSON_ERROR_NONE) {
                return $params;
            } else {
                throw (new CommandException(__('can not parse param $0',$param)));
            }
        } elseif (preg_match('/^\=s(erialize)?\:(\:)?(.+)$/', $param, $matchs)) {
            if (isset($matchs[2]) && $matchs[2]) {
                $params = unserialize(base64_decode($matchs[3]));
            } else {
                $params = unserialize($matchs[3]);
            }
            if (is_object($params)) {
                return [$params];
            }
            return $params;
        } else {
            $params = explode(',', trim($param, ','));
            foreach ($params as $index=>$value) {
                $params[$index]=trim($value);
            }
            return $params;
        }
    }
    
    public static function newClassInstance(string $class)
    {
        if (preg_match('/^([\w\\\\\/.]+) (\( ( (?>[^()]+) | (?2) )* \)) /ux', $class, $matchs)) {
            list($str, $className, $constructParams) = $matchs;
            if (preg_match('/\((.+)\)/', $constructParams, $tmp)) {
                $constructParams=$tmp[1];
            }
            $params=self::parseParam($constructParams);
            $className = Autoloader::realName($className);
            $classRef= new \ReflectionClass($className);
            return $classRef->newInstanceArgs($params);
        }
        $className = Autoloader::realName($class);
        return new $className;
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
    public function __toString()
    {
        return $this->cmdStr ?? $this->name ?? __CLASS__;
    }
}
