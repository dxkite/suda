<?php
namespace suda\core;

use suda\tool\Command;
use suda\tool\Json;

class Router
{
    protected $mapper;
    protected $matchs=[];
    protected $types;
    protected $urltype=['int'=>'\d+','string'=>'[^\/]+','longstring'=>'.+'];
    protected static $router=null;
    protected $routers=[];
    
    private function __construct()
    {
        Hook::listen('system:404', 'Router::error404');
        Hook::listen('Router:dispatch::error', 'Router::error404');
    }

    public static function getInstance()
    {
        if (is_null(self::$router)) {
            self::$router=new Router;
        }
        return self::$router;
    }

    public function load(string $module)
    {
        $routers=[];
        // 加载普通路由
        if (Storage::exist(MODULES_DIR.'/'.$module.'/resource/config/router.json')){
            $routers=array_merge($routers,Json::loadFile(MODULES_DIR.'/'.$module.'/resource/config/router.json'));
        }
        // 加载管理路由
        if (Storage::exist(MODULES_DIR.'/'.$module.'/resource/config/router.json')){
            $routers=array_merge($routers,Json::loadFile(MODULES_DIR.'/'.$module.'/resource/config/router_admin.json'));
        }
        array_walk($routers, function (&$router) use ($module) {
            $router['module']=$module;
        });
        $this->routers=array_merge($this->routers, $routers);
    }

    protected function loadModulesRouter()
    {
        $modules=Config::get('app.modules',[]);
        foreach ($modules as $module) {
            self::load($module);
        }
    }

    public function watch(string $name, string $url)
    {
        $this->matchs[$name]=self::buildMatch($name, $url);
    }
    protected function matchRouterMap()
    {
        $request=Request::getInstance();
        foreach ($this->matchs as $name=>$preg) {
            if (preg_match('/^'.$preg.'$/', $request->url(), $match)) {
                
                // 检验接口参数
                if (isset($this->routers[$name]['method']) && count($this->routers[$name]['method'])>0) {
                    array_walk($this->routers[$name]['method'], 'strtolower');
                    // 方法不匹配
                    if (!in_array(strtolower($request->method()), $this->routers[$name]['method'])) {
                        continue;
                    }
                }
                
                array_shift($match);
                if (count($match)>0) {
                    foreach ($this->types[$name] as $param_name =>$type) {
                        $value=array_shift($match);
                        if ($type==='int') {
                            $value=intval($value);
                        } else {
                            $value=urldecode($value);
                        }
                        $request->set($param_name, $value);
                    }
                }
                return $name;
            }
        }
        return false;
    }

    protected function buildRouterMap()
    {
        foreach ($this->routers as $name => $router) {
            self::watch($name, $router['visit']);
        }
    }



    protected function buildMatch(string $name, string $url)
    {
        $types=&$this->types;
        $urltype=$this->urltype;
        $url=preg_replace('/([\/\.\\\\\+\*\[\^\]\$\(\)\=\!\<\>\|\-])/', '\\\\$1', $url);
        $url=preg_replace_callback('/\{(?:(\w+)(?::(\w+)))\}/', function ($match) use ($name, &$types, $urltype) {
            $size=isset($types[$name])?count($types[$name]):0;
            $param_name=$match[1]!==''?$match[1]:$size;
            $param_type=isset($match[2])?$match[2]:'url';
            $types[$name][$param_name]=$param_type;
            if (isset($urltype[$param_type])) {
                return '('.$urltype[$param_type].')';
            } else {
                return '(.+)';
            }
        }, $url);
        return $url;
    }

    public function buildUrl(string $name, array $values)
    {
        $url=DIRECTORY_SEPARATOR === '/'?'/':'/?/';
        if (isset($this->routers[$name])) {
            $url=preg_replace_callback('/\{(?:(\w+)(?::(\w+)))\}/', function ($match) use ($name, $values) {
                $param_name=$match[1];
                $param_type=isset($match[2])?$match[2]:'url';
                if (isset($values[$param_name])) {
                    if ($param_type==='int') {
                        return intval($values[$param_name]);
                    }
                    return $values[$param_name];
                } else {
                    return '';
                }
            }, $this->routers[$name]['url']);
        } else {
            return '_undefine_router';
        }
        return $url;
    }


    public function dispatch()
    {
        self::loadModulesRouter();
        self::buildRouterMap();
        // Hook前置路由（自定义过滤器|自定义路由）
        if (Hook::execIf('Router:dispatch::before', [Request::getInstance()], true)) {
            if (($router_name=self::matchRouterMap())!==false) {
                self::runRouter($this->routers[$router_name]);
            } else {
                Hook::exec('system:404');
            }
        } else {
            Hook::execTail('Router:dispatch::error');
        }
    }
    protected static function runRouter(array $router)
    {
        if (! (isset($router['ob']) && $router['ob']===false) ){
            Response::obStart();
        }
        (new \suda\tool\Command(Config::get('app.application').'::activeModule'))->exec([$router['module']]);
        (new \suda\tool\Command($router['class'].'->onRequest'))->exec([Request::getInstance()]);
    }

    public static function error404()
    {
        $render=new Response;
        $render->state(404);
        $render->display('suda:error404', ['path'=>Request::url()]);
    }
}
