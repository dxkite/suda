<?php
namespace suda\core;

use suda\tool\Command;
use suda\tool\Json;
use suda\tool\ArrayHelper;

// TODO:路由强化
// TODO:路由模块化（添加命名空间）

class Router
{
    protected $mapper;
    protected $matchs=[];
    protected $types;
    protected static $urltype=['int'=>'\d+','string'=>'[^\/]+','url'=>'.+'];
    protected static $router=null;
    protected $routers=[];

    private function __construct()
    {
        Hook::listen('system:404', 'Router::error404');
        Hook::listen('Router:dispatch::error', 'Router::error404');
        self::loadModulesRouter();
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
        $simple_routers=[];
        $admin_routers=[];
        $module_dir=Application::getModuleDir($module);
        _D()->trace(_T('load module:%s [%s] path:%s', $module, Application::getModuleFullName($module), MODULES_DIR.'/'.$module_dir));
        $prefix= Application::getModulePrefix($module);
        $module=Application::getModuleFullName($module);
        $admin_prefix='';
        if (is_array($prefix)) {
            $admin_prefix=$prefix['admin'] ?? array_shift($prefix);
            $prefix=$prefix['simple'] ?? array_shift($prefix);
        }


        if (Storage::exist($file=MODULES_DIR.'/'.$module_dir.'/resource/config/router.json')) {
            $simple_routers= self::loadModuleJson($module, $file);
            _D()->trace(_T('loading simple route from file %s', $file));
            array_walk($simple_routers, function (&$router) use ($module, $prefix) {
                if (!is_null($prefix)) {
                    $router['visit']=$prefix.$router['visit'];
                }
                $router['visit']='/'.trim($router['visit'], '/');
                $router['module']=$module;
            });
        }

        // 加载后台路由
        if (Storage::exist($file=MODULES_DIR.'/'.$module_dir.'/resource/config/router_admin.json')) {
            $admin_routers= self::loadModuleJson($module, $file);
            _D()->trace(_T('loading admin route from file  %s', $file));
            array_walk($admin_routers, function (&$router) use ($module, $admin_prefix) {
                $prefix= conf('app.admin', '/admin');
                if (!is_null($admin_prefix)) {
                    $prefix = $prefix . $admin_prefix;
                }
                $router['visit']=$prefix.$router['visit'];
                $router['visit']='/'.trim($router['visit'], '/');
                $router['module']=$module;
            });
        }
       
        $this->routers=array_merge($this->routers, $admin_routers, $simple_routers);
    }
    protected function loadModuleJson(string $module, string $jsonfile)
    {
        $routers=Json::loadFile($jsonfile);
        $router=[];
        foreach ($routers as $name => $value) {
            $router[$module.':'.$name]=$value;
        }
        return $router;
    }

    
    protected function loadFile()
    {
        $this->routers=require TEMP_DIR.'/router.cache.php';
        $this->matchs=require TEMP_DIR.'/matchs.cache.php';
    }
    protected function saveFile()
    {
        ArrayHelper::export(TEMP_DIR.'/router.cache.php', '_router', $this->routers);
        ArrayHelper::export(TEMP_DIR.'/matchs.cache.php', '_matchs', $this->matchs);
    }
    protected function loadModulesRouter()
    {
        if (conf('debug')) {
            $modules=Application::getLiveModules();
            foreach ($modules as $module) {
                self::load($module);
            }
            self::buildRouterMap();
            // 缓存路由信息
            self::saveFile();
        } else {
            // 提取路由信息
            self::loadFile();
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
            // _D()->d('url:'.$request->url().'; preg:'.'/^'.$preg.'$/');
            if (preg_match('/^'.$preg.'$/', $request->url(), $match)) {
                // 检验接口参数
                if (isset($this->routers[$name]['method']) && count($this->routers[$name]['method'])>0) {
                    array_walk($this->routers[$name]['method'], function ($value) {
                        return strtoupper($value);
                    });
                    // 方法不匹配
                    if (!in_array(strtoupper($request->method()), $this->routers[$name]['method'])) {
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
                        // 填充$_GET
                        $_GET[$param_name]=$value;
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
        $urltype=self::$urltype;
        $url=preg_replace('/([\/\.\\\\\+\*\(\^\)\?\$\=\!\<\>\-])/', '\\\\$1', $url);
        $url=preg_replace('/\[(\S+)\]/', '(?:$1)?', $url);
        $url=preg_replace_callback('/\{(?:(\w+)(?::(\w+))?)\}([?])?/', function ($match) use ($name, &$types, $urltype) {
            $size=isset($types[$name])?count($types[$name]):0;
            $param_name=$match[1]!==''?$match[1]:$size;
            $param_type=  $match[2] ?? 'string';
            $ignore=isset($match[3])?'?':'';
            $types[$name][$param_name]=$param_type;
            if (isset($urltype[$param_type])) {
                return '('.$urltype[$param_type].')'.$ignore;
            } else {
                return '(.+)'.$ignore;
            }
        }, $url);
        return $url;
    }

    public function buildUrl(string $name, array $values=[])
    {
        preg_match('/^(?:(.+?)[:])?(.+)$/', $name, $match);
        $name=$match[2];
        $module=$match[1]?Application::getModuleFullName($match[1]):Application::getActiveModule();
        $name=$module.':'.$name;
        $url= '';
        if (isset($this->routers[$name])) {
            // 路由存在
            $url.=preg_replace('/[?|]/', '\\\1', $this->routers[$name]['visit']);
            $url=preg_replace_callback('/\{(?:(\w+)(?::(\w+))?)\}/', function ($match) use ($name, & $values) {
                $param_name=$match[1];
                $param_type= $match[2] ?? 'url';
                if (isset($values[$param_name])) {
                    if ($param_type==='int') {
                        $val= intval($values[$param_name]);
                    }
                    $val=$values[$param_name];
                    unset($values[$param_name]);
                    return $val;
                } else {
                    return '';
                }
            }, preg_replace('/\[(.+?)\]/', '$1', $url));
        } else {
            _D()->warning(_T('get url for %s failed,module:%s args:%s', $name, $module, json_encode($values)));
            return '#the-router-['.$name.']-is-undefined--please-check-out-router-list';
        }
        if (count($values)) {
            return Request::getInstance()->baseUrl(). ltrim($url, '/').'?'.http_build_query($values, 'v', '&', PHP_QUERY_RFC3986);
        }
        return Request::getInstance()->baseUrl(). ltrim($url, '/');
    }


    public function dispatch()
    {
        self::buildRouterMap();
        // Hook前置路由（自定义过滤器|自定义路由）
        if (Hook::execIf('Router:dispatch::before', [Request::getInstance()], true)) {
            if (($router_name=self::matchRouterMap())!==false) {
                Response::setName($router_name);
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
        (new \suda\tool\Command(Config::get('app.application', 'suda\\core\\Application').'::activeModule'))->exec([$router['module']]);
        _D()->time('request');
        (new \suda\tool\Command($router['class'].'->onRequest'))->exec([Request::getInstance()]);
        _D()->timeEnd('request');
    }

    public static function error404()
    {
        $render=new class extends Response {
            public   function onRequest(Request $request)
            {
                $this->state(404);
                $this->page('suda:error404', ['title'=>'404 Error','path'=>$request->url()])->render();
            }
        };
        $render->onRequest(Request::getInstance());
    }
}
