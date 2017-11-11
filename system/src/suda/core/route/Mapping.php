<?php
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

    protected $antiPrefix=false;
    protected $hidden=false;
    protected $dynamic=false;

    const ROLE_ADMIN=0;
    const ROLE_SIMPLE=1;

    protected static $urlType=['int'=>'\d+','string'=>'[^\/]+','url'=>'.+'];
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
        $matchExp=$ignoreCase?'/^'.$this->mapping.'$/i':'/^'.$this->mapping.'$/';
        if (preg_match($matchExp, $request->url(), $match)) {
  
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
                    $_GET[$paramName]=$value;
                    $request->set($paramName, $value);
                }
            }
            // 自定义过滤
            if (!hook()->execIf('Router:filter', [$this->getFullName(),$this], false)) {
                return false;
            }
            return true;
        } else {
            return false;
        }
    }

    public function run()
    {
        self::$current=$this;
        return (new Command($this->callback))->exec([Request::getInstance()]);
    }

    public function build()
    {
        $urlMapping='/'.trim($this->url, '/');
        if (!$this->antiPrefix) {
            $urlMapping='/'.trim($this->getPrefix().$urlMapping, '/');
        }
        $this->mapping=$this->buildMatch($urlMapping);
        return $this;
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

    public function createUrl(array $args)
    {
        $url='/'.trim($this->url, '/');
        if (!$this->antiPrefix) {
            $url='/'.trim($this->getPrefix().$this->url, '/');
        }
        $url=preg_replace('/[?|]/', '\\\1', $url);
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
        }, preg_replace('/\[(.+?)\]/', '$1', $url));
        if (count($args)) {
            return Request::getInstance()->baseUrl(). trim($url, '/').'?'.http_build_query($args, 'v', '&', PHP_QUERY_RFC3986);
        }
        return Request::getInstance()->baseUrl(). trim($url, '/');
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
        $url=preg_replace('/\[(\S+)\]/', '(?:$1)?', $url);
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
        $mapping= new self($name, $json['visit'], $json['class'].'->onRequest', $module, $json['method']??[], $role);
        $mapping->antiPrefix=isset($json['anti-prefix'])?$json['anti-prefix']:false;
        $mapping->hidden=isset($json['hidden'])?$json['hidden']:false;
        $mapping->param= $json['param'] ?? null;
        $mapping->build();
        return $mapping;
    }
}
