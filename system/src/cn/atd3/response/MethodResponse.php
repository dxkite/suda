<?php
namespace cn\atd3\response;

use ReflectionClass;
use ReflectionMethod;
use Exception;
use suda\core\Request;

/**
 * 自动调用函数接口来响应
 */
abstract class MethodResponse extends \suda\core\Response
{
    // 参数生成
    const PARAM_GET=1;
    const PARAM_POST=2;
    const PARAM_JSON=3;
    const PARAM_ALL=4;
    
    protected $export;
    protected $request;
    protected $default='__default';

    public function __construct()
    {
        parent::__construct();
        $this->export=$this->getExportMethods();
    }
    /**
     * 分发路由到相关控制函数
     *
     * @param Request $request
     * @return void
     */
    public function onRequest(Request $request)
    {
        $this->request=$request;
        $method=$request->get()->method($this->default);
        if (isset($this->export[$method]['comment']) && preg_match('/@paramSource\s+(get|post|json|all)\s*$/ims', $this->export[$method]['comment'], $match)) {
            $type=$match[1];
            $alias=[
                    'get'=>MethodResponse::PARAM_GET,
                    'post'=>MethodResponse::PARAM_POST,
                    'json'=>MethodResponse::PARAM_JSON,
                    'all'=>MethodResponse::PARAM_ALL,
                ];
            $param_arr=$this->getParams($alias[strtolower($type)]);
        } else {
            $param_arr=$this->getParams();
        }
        if (isset($this->export[$method])) {
            return $this->runMethod($this->export[$method]['callback'], $param_arr);
        } else {
            return $this->runMethod([$this, $this->default], $param_arr);
        }
    }
    abstract public function __default();
    /**
     * 获取导出的接口
     *
     * @return void
     */
    public function getExportMethods()
    {
        $class=new ReflectionClass($this);
        $export=array();
        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $name=$method->getShortName();
            if ($method->getDeclaringClass()->getName()===get_class($this)) {
                $export[$name]['comment']=$method->getDocComment();
                $export[$name]['callback']=[get_class($this),$name];
            }
        }
        _D()->info($export);
        return $export;
    }

    public function runMethod($method, array $param_arr)
    {
        if (count($method)>1) {
            if ($method[0]===get_class($this)) {
                $object=$this;
            } else {
                $object=(new ReflectionClass($method[0]))->newInstance();
            }
            $method=new ReflectionMethod($method[0], $method[1]);
            return $method->invoke($object, $param_arr);
        } else {
            return call_user_func($method, $param_arr);
        }
    }

    public function getParams(int $type=MethodResponse::PARAM_GET):array
    {
        switch ($type) {
            case MethodResponse::PARAM_GET:
                return $this->request->get()->_getVar();
            case MethodResponse::PARAM_POST:
                return $this->request->post()->_getVar();
            case MethodResponse::PARAM_JSON:
                return $this->request->json();
            default:
                return array_merge($this->request->get()->_getVar(), $this->request->post()->_getVar(), $this->request->json());
        }
    }
}
