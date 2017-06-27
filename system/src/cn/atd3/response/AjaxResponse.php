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
 * @version    since 1.2.5
 */

namespace cn\atd3\response;

use ReflectionMethod;
use cn\atd3\exception\AjaxException;
use Exception;
use ReflectionClass;

class AjaxResponse extends MethodResponse
{
    protected $defaultParams=[MethodResponse::PARAM_JSON];
    
    public function __construct()
    {
        parent::__construct();
    }

    public function __default()
    {
        return $this->json($this->getHelpJson());
    }

    protected function getHelpJson()
    {
        $methods=$this->getExportMethods();
        $help=[];
        foreach ($methods as $method) {
            $method=$this->getReflectionMethod($method['callback']);
            $name=$method->getShortName();
            $help[$name]['doc']=$method->getDocComment();
            foreach ($method->getParameters() as $param) {
                $help[$name]['parameters'][$param->getName()]['pos']=$param->getPosition();
                if ($param->hasType()) {
                    $help[$name]['parameters'][$param->getName()]['type']=$param->getType()->__toString();
                }
                if ($param->isDefaultValueAvailable()) {
                    $help[$name]['parameters'][$param->getName()]['default']=$param->getDefaultValue();
                }
            }
        }
        return $help;
    }
    
    protected function getReflectionMethod($method)
    {
        if (count($method)>1) {
            return new ReflectionMethod($method[0], $method[1]);
        } else {
            return new ReflectionFunction($method);
        }
    }

    public function runMethod($method, array $param_arr)
    {
        $method=$this->getReflectionMethod($method);
        try {
            $param_arr=$this->paramsCheck($method, $param_arr);
        } catch (AjaxException $e) {
            return $this->data($e->getData(), $e->getName(), $e->getMessage(), $e->getCode());
        }
        try {
            if ($method->getShortName()==$this->defaultMethod) {
                return $this->{$this->defaultMethod}();
            } else {
                $data=$this->invokeMethod($method, $param_arr);
            }
        } catch (AjaxException $e) {
            return $this->data($e->getData(), $e->getName(), $e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            return $this->data($param_arr, get_class($e), $e->getMessage(), $e->getCode());
        }
        return $this->data($data);
    }

    protected function invokeMethod($method, array $param_arr)
    {
        if ($method instanceof ReflectionMethod) {
            if ($method->getDeclaringClass()===get_class($this)) {
                $object=$this;
            } else {
                $class=$method->getDeclaringClass();
                $object=$class->newInstance();
            }
            return $method->invokeArgs($object, $param_arr);
        }
        return $method->invokeArgs($param_arr);
    }

    protected function paramsCheck($method, $params)
    {
        $args=[];
        // 压入调用参数
        foreach ($method->getParameters() as $param) {
            $name=$param->getName();
            $pos=$param->getPosition();
            if (isset($params[$name])) {
                if ($param->hasType()) {
                    $val=$params[$name];
                    if (@settype($val, $param->getType()->__toString())) {
                        $args[$pos]=$val;
                    } else {
                        throw (new AjaxException(__('参数 %s 无法转化成 %s 类型！', $name, $param->getType()->__toString()), -1))->setName('paramTypeCastException');
                    }
                } else {
                    $args[$pos]=$params[$name];
                }
            } elseif (!$param->isDefaultValueAvailable()) {
                throw (new AjaxException(__('参数错误，需要参数: %s', $name), -1))->setName('paramError')->setData(['name'=>$name, 'pos'=>$pos]);
            }
        }
        return $args;
    }

    protected function data($data, string $error=null, string $message=null, int $erron=0)
    {
        return $this->json([
            'error'=>$error,
            'errno'=>$erron,
            'message'=>$message,
            'data'=>$data,
        ]);
    }
}
