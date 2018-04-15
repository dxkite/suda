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
 * @version    since 1.2.12
 */

namespace suda\core\route;

use suda\tool\Command;
use suda\core\Request;

class Mapping
{
    protected $method=[];
    protected $url;
    protected $mapping;
    protected $callback;
    protected $module;
    protected $name;
    protected $role;
    protected $types;
    protected $param;
    protected $value;

    protected $host = null;
    protected $port;
    protected $scheme;
 
    protected $antiPrefix=false;
    protected $hidden=false;
    protected $dynamic=false;
    
    const ROLE_ADMIN=0;
    const ROLE_SIMPLE=1;
    protected static $urlType=['int'=>'\d+', 'string'=>'[^\/]+','url'=>'.+'];
    public static $current;

    public function __construct(string $name, string $url, string $callback, string $module, array $method=[], int $role=self::ROLE_SIMPLE)
    {
        $this->module=app()->getModuleFullName($module);
        $this->name=$name;
        array_walk($method, function ($value) {
            return strtoupper($value);
        });
        $this->method= $method;
        $this->callback=$callback;
        $this->role=$role;
        $this->url=$url;
    }

    public function match(Request $request, bool $ignoreCase=true)
    {
        if ($this->hidden) {
            return false;
        }
        // 方法不匹配
        if (count($this->method)>0 && !in_array(strtoupper($request->method()), $this->method)) {
            return false;
        }
        $paramGet=[];
        if ($this->matchUrlValue($request->url(), $ignoreCase, $paramGet)) {
            // 自定义过滤
            if (hook()->execIf('Router:filter', [$this->getFullName(),$this], true)) {
                return false;
            }
            foreach ($paramGet as $paramName=>$value) {
                $request->set($paramName, $value);
                $_GET[$paramName]=$value;
            }
            return true;
        }
        return false;
    }

    public function matchUrlValue(string $url, bool $ignoreCase, array &$valueGet)
    {
        $matchExp=$ignoreCase?'/^'.$this->mapping.'$/i':'/^'.$this->mapping.'$/';
        if (preg_match($matchExp, $url, $match)) {
            // 检验接口参数
            array_shift($match);
            if (count($match)>0) {
                foreach ($this->types as $paramName =>$type) {
                    $value=array_shift($match);
                    if ($type==='int') {
                        $value=intval($value);
                    } else {
                        $value=urldecode($value);
                    }
                    // 填充$_GET
                    $valueGet[$paramName]=$value;
                }
            }
            return true;
        }
        return false;
    }

    public function run()
    {
        self::$current=$this;
        return (new Command($this->callback))->exec([Request::getInstance()->setMapping($this)]);
    }

    public function build()
    {
        $urlMapping=rtrim($this->url, '/');
        if (!$this->antiPrefix) {
            $urlMapping='/'.trim(rtrim($this->getPrefix(), '/').$urlMapping, '/');
        }
        if (empty($urlMapping)) {
            $urlMapping='/';
        }
        $this->mapping=$this->buildMatch($urlMapping);
        return $this;
    }

    /**
     * 判断路由是否为指定路由
     *
     * @param string $that
     * @return boolean
     */
    public function is(string $that)
    {
        list($module, $name)=router()->parseName($that);
        return app()->getModuleFullName($module).':'.$name == $this->getFullName();
    }
    
    /**
     * 判断路由似乎否是在指定模块中
     *
     * @param string $that
     * @return void
     */
    public function inModule(string $that)
    {
        return  app()->getModuleFullName($that) == $this->module;
    }

    public function getFullName()
    {
        return $this->module.':'.$this->name;
    }
    
    public function setParam($param)
    {
        $this->param=$param;
        return $this;
    }

    public function getParam()
    {
        return $this->param;
    }

    public function setValue($value)
    {
        $this->value=$value;
        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }
    
    public function setCallback(string $callback)
    {
        $this->callback=$callback;
        return $this;
    }

    public function setModule(string $module)
    {
        $this->module=$module;
        return $this;
    }
    
    public function setMethod(string $method)
    {
        $this->method=$method;
        return $this;
    }

    public function isDynamic()
    {
        return $this->dynamic;
    }
    
    public function isHidden()
    {
        return $this->hidden;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function getModule()
    {
        return $this->module;
    }

    public function getTypes()
    {
        return $this->types;
    }
    
    public function setAntiPrefix(bool $set=true)
    {
        $this->antiPrefix=$set;
        return $this;
    }
    
    public function setDynamic(bool $set=true)
    {
        $this->dynamic=$set;
        return $this;
    }

    public function setHidden(bool $set=true)
    {
        $this->hidden=$set;
        return $this;
    }

    public function setMapping(string $mapping)
    {
        $this->mapping=$mapping;
        return $this;
    }

    public function setUrl(string $url)
    {
        $this->url=$url;
        return $this;
    }
    
    public function getUrl()
    {
        return  $this->url;
    }

    public function getUrlTemplate()
    {
        $url='/'.trim($this->url, '/');
        if (!$this->antiPrefix) {
            $url='/'.trim($this->getPrefix().$this->url, '/');
        }
        return Request::getInstance()->baseUrl(). trim($url, '/');
    }

    public function createUrl(array $args, bool $query=true, array $queryArr=[])
    {
        $url='/'.trim($this->url, '/');
        if (!$this->antiPrefix) {
            $url='/'.trim($this->getPrefix().$this->url, '/');
        }
        $url=preg_replace('/[?|]/', '\\\1', $url);
        // 如果没有设置忽略部分则去除忽略部分的表达式匹配
        $url=preg_replace_callback('/\[(.+?)\]/', function ($match) use ($args) {
            if (preg_match('/\{(?:(\w+)(?::(\w+))?)(?:=(\w+))?\}/', $match[1], $paramsArray)) {
                if (is_array($paramsArray[1])) {
                    foreach ($paramsArray[1] as $name) {
                        if (!array_key_exists($name, $args)) {
                            return '';
                        }
                    }
                } elseif (!array_key_exists($paramsArray[1], $args)) {
                    return '';
                }
                return $match[1];
            } else {
                return '';
            }
        }, $url);
        // 匹配设置的参数
        $url=preg_replace_callback('/\{(?:(\w+)(?::(\w+))?)(?:=(\w+))?\}/', function ($match) use (& $args) {
            $param_name=$match[1];
            $param_type= $match[2] ?? 'url';
            $param_default=$match[3]??'';
            if (isset($args[$param_name])) {
                if ($param_type==='int') {
                    $val= intval($args[$param_name]);
                }
                $val=$args[$param_name];
                unset($args[$param_name]);
                return $val;
            } else {
                return $param_default;
            }
        }, $url);
        if (count($args) && $query) {
            return $this->getBaseUrl(). trim($url, '/').'?'.http_build_query($args, 'v', '&', PHP_QUERY_RFC3986);
        }
        return $this->getBaseUrl(). trim($url, '/'). (count($queryArr)?'?'.http_build_query($queryArr, 'v', '&', PHP_QUERY_RFC3986):'');
    }
    
    public function getBaseUrl()
    {
        if (is_null($this->host)) {
            return Request::getInstance()->baseUrl();
        }
        return $this->scheme.'://'. $this->host.($this->port!=80?':'. $this->port:'').'/';
    }

    public function getPrefix()
    {
        $prefix=app()->getModulePrefix($this->module)??'';
        $admin_prefix='';
        if (is_array($prefix)) {
            if (in_array(key($prefix), ['admin','simple'], true)) {
                $admin_prefix=$prefix['admin'] ?? '';
                $prefix=$prefix['simple'] ?? '';
            } else {
                $admin_prefix=count($prefix)?array_shift($prefix):'';
                $prefix=count($prefix)?array_shift($prefix):'';
            }
        }
        if ($this->role == self::ROLE_ADMIN) {
            return conf('app.admin', '/admin').$admin_prefix;
        } else {
            return $prefix;
        }
    }

    protected function buildMatch(string $url)
    {
        $types=&$this->types;
        $urltype=self::$urlType;
        // 转义字符
        $url=preg_replace('/([\/\.\\\\\+\*\(\^\)\?\$\!\<\>\-])/', '\\\\$1', $url);
        // 添加忽略
        $url=preg_replace('/(\[)([^\[\]]+)(?(1)\])/', '(?:$2)?', $url);
        // 编译页面参数
        $url=preg_replace_callback('/\{(?:(\w+)(?::(\w+))?)(?:=(\w+))?\}([?])?/', function ($match) use (&$types, $urltype) {
            $size=isset($types)?count($types):0;
            $param_name=$match[1]!==''?$match[1]:$size;
            $param_type=  $match[2] ?? 'string';
            $ignore=isset($match[4])?'?':'';
            $types[$param_name]=$param_type;
            if (isset($urltype[$param_type])) {
                return '('.$urltype[$param_type].')'.$ignore;
            } else {
                return '(.+)'.$ignore;
            }
        }, $url);
        return $url;
    }

    public static function createFromRouteArray(int $role, string $module, string $name, array $json)
    {
        $mapping= new self($name, $json['url']??$json['visit'], $json['class'].'->onRequest', $module, $json['method']??[], $role);
        $mapping->antiPrefix=isset($json['anti-prefix'])?$json['anti-prefix']:false;
        $mapping->hidden= $json['disable'] ?? $json['hidden'] ?? false;
        $mapping->param= $json['param'] ?? null;
        if (isset($json['host'])) {
            $mapping->host = $json['host'];
            $mapping->scheme = $json['scheme'] ?? $_SERVER['REQUEST_SCHEME'] ?? 'http';
            $mapping->port = $json['port'] ??$_SERVER["SERVER_PORT"]?? 80;
        }
        $mapping->build();
        return $mapping;
    }

    public static function current()
    {
        return  self::$current;
    }
}
