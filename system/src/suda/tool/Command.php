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

use ReflectionClass;
use suda\core\Autoloader;
use suda\exception\CommandException;

/**
 * 可执行命令表达式
 *
 */
class Command
{
    public $command =null;
    public $file = null;
    public $static=false;
    public $params=[];
    public $funcParam=[];
    public $constructParam=[];
    public $name;
    public $cmdstr;
    public $singleFile=false;

    public function __construct($command, array $params=[])
    {
        $this->command = is_string($command)? $this->parseCommand($command):$command;
        $this->params  = $params;
        $this->name    = $this->getCommandName($command);
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
        debug()->trace(__('exec command $0 with args', $this->name), $params);
        // 集合所有参数
        if (count($params)) {
            $this->params=$params;
        }
        // 文件引入
        if (!is_null($this->file) && $this->singleFile) {
            // 文件参数引入
            array_unshift($params, $this->file);
            $_SERVER['argv']=$params;
            $_SERVER['args']=count($params);
            return include $this->file;
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
        if (!is_null($this->command)) {
            // 是函数调用&指定了文件&函数不存在
            if (is_string($this->command) && !function_exists($this->command) && !is_null($this->file)) {
                require_once $this->file;
            }
            // 调用接口
            elseif (is_array($this->command)) {
                if (!is_object($this->command[0])) {
                    if ($this->static) {
                    } else {
                        if (!empty($this->constructParam)) {
                            $class = new ReflectionClass($this->command[0]);
                            $this->command[0]= $class->newInstanceArgs($this->constructParam);
                        } else {
                            $this->command[0]=new $this->command[0];
                        }
                    }
                }
            }
            return static::_absoluteCall($this->command, $this->params);
        } else  {
            throw (new CommandException(__('invaild command: $0', $this->cmdstr)))->setCmd($this);
        }
    }

    public function args()
    {
        return $this->exec(func_get_args());
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
            throw (new CommandException(__('invaild command name: $0', $name)))->setCmd($this);
        }
        return Autoloader::realName($name);
    }
    
    protected static function parseParameter(string $param)
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
                throw (new CommandException(__('can not parse param $0', $param)));
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
        $this->cmdstr= $command;
        $fileStart = \strrpos($command, '@');
        // for @file
        if ($fileStart > 0) {
            $this->file = substr($command, $fileStart+1);
            $command = substr($command, 0, $fileStart);
        }
        if ($fileStart === 0) {
            $this->singleFile = true;
            return null;
        }
        // for parameter list
        list($command, $parameter) = self::splitParameter($command);
        // for method
        $dynmicsMethod = strpos($command, '->');
        $splitLength = strpos($command, '#');
        $methodStart = $splitLength ?: strpos($command, '::') ?: $dynmicsMethod;
        // static method
        $this->static = !$dynmicsMethod;
        $this->funcParam = self::buildParameter($parameter);
        if ($methodStart > 0) {
            $splitLength = $splitLength > 0 ? 1:2;
            $methodName = substr($command, $methodStart + $splitLength);
            $command = substr($command, 0, $methodStart);
            list($command, $constructParameter) = self::splitParameter($command);
            $this->constructParam = self::buildParameter($constructParameter);
            return [$this->buildCommandName($command),$methodName];
        } else {
            return $this->buildCommandName($command);
        }
    }

    
    public static function newClassInstance(string $class)
    {
        if (preg_match('/^([\w\\\\\/.]+) (\( ( (?>[^()]+) | (?2) )* \)) /ux', $class, $matchs)) {
            list($str, $className, $constructParams) = $matchs;
            if (preg_match('/\((.+)\)/', $constructParams, $tmp)) {
                $constructParams=$tmp[1];
            }
            $params=self::buildParameter($constructParams);
            $className = Autoloader::realName($className);
            $classRef= new ReflectionClass($className);
            return $classRef->newInstanceArgs($params);
        }
        $className = Autoloader::realName($class);
        return new $className;
    }

    /**
     * 绝对调用函数，可调用类私有和保护函数
     *
     * @param mixed $command
     * @param mixed $params
     * @return mixed
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

    protected static function getCommandName($command):string
    {
        if (\is_string($command)) {
            return $command;
        }
        if (\is_array($command)) {
            if (\is_object($command[0])) {
                return \get_class($command[0]).'->'.$command[1];
            } else {
                return $command[0].'::'.$command[1];
            }
        }
        return 'Closure Object()';
    }

    public function __toString()
    {
        return $this->cmdstr ?? $this->name ?? __CLASS__;
    }
}
