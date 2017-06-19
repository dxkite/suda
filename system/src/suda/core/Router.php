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
    protected $types=[];
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
        _D()->trace(__('load module:%s [%s] path:%s', $module, Application::getModuleFullName($module), MODULES_DIR.'/'.$module_dir));
        $prefix= Application::getModulePrefix($module);
        $module=Application::getModuleFullName($module);
        $admin_prefix='';
        if (is_array($prefix)) {
            // TODO: admin->backend simple->frontend
            $admin_prefix=$prefix['admin'] ?? array_shift($prefix);
            $prefix=$prefix['simple'] ?? array_shift($prefix);
        }
        // _D()->debug($prefix);
        if (Storage::exist($file=MODULES_DIR.'/'.$module_dir.'/resource/config/router.json')) {
            $simple_routers= self::loadModuleJson($module, $file);
            _D()->trace(__('loading simple route from file %s', $file));
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
            _D()->trace(__('loading admin route from file  %s', $file));
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
        $this->types=require TEMP_DIR.'/types.cache.php';
        $this->matchs=require TEMP_DIR.'/matchs.cache.php';
    }

    protected function saveFile()
    {
        ArrayHelper::export(TEMP_DIR.'/router.cache.php', '_router', $this->routers);
        $type=ArrayHelper::export(TEMP_DIR.'/types.cache.php', '_types', $this->types);
        ArrayHelper::export(TEMP_DIR.'/matchs.cache.php', '_matchs', $this->matchs);
        _D()->info(__('export %d', $type));
    }

    protected function loadModulesRouter()
    {
        // 如果DEBUG模式
        if (conf('debug', false)) {
            self::prepareRouterInfo();
        } else {
            if(!self::routerCached()){
                self::prepareRouterInfo();
            }
            self::loadFile();
        }
    }
    public function routerCached(){
        if(!file_exists(TEMP_DIR.'/router.cache.php')) return false;
        if(!file_exists(TEMP_DIR.'/types.cache.php')) return false;
        if(!file_exists(TEMP_DIR.'/matchs.cache.php')) return false;
    }
    public function prepareRouterInfo()
    {
        $modules=Application::getLiveModules();
        foreach ($modules as $module) {
            self::load($module);
        }
        self::buildRouterMap();
        // 缓存路由信息
        self::saveFile();
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
    /**
    * 解析模板名
    */
    public static function parseName(string $name)
    {
        // MODULE_NAME_PREG
        // [模块前缀名称/]模块名[:版本号]:(模板名|路由ID)
        preg_match('/^((?:[a-zA-Z0-9_-]+\/)?[a-zA-Z0-9_-]+)(?::([^:]+))?(?::(.+))?$/', $name, $match);
        _D()->debug($match);

        // 单纯路由或者模板
        if (isset($match[1]) && count($match)==2) {
            $module=Application::getActiveModule();
            $info=$match[0];
        } else {
            $info=isset($match[3])?$match[3]:$match[2];
            $module=isset($match[3])?
                        (isset($match[1])?
                            $match[1].(
                                $match[2]?
                                ':'.$match[2]
                                :'')
                            :Application::getActiveModule())
                    :$match[1];
        }
        return [$module,$info];
    }
    
    public function buildUrlArgs(string $name, array $args)
    {
        list($module, $name)=self::parseName($name);
        $module=Application::getModuleFullName($module);
        $name=$module.':'.$name;
        if (isset($this->types[$name])) {
            $keys=array_keys($this->types[$name]);
            $values=[];
            foreach ($keys as $key) {
                if (count($args)) {
                    $values[$key]=array_shift($args);
                } else {
                    break;
                }
            }
            return $values;
        }
        return [];
    }

    public function buildUrl(string $name, array $values=[])
    {
        list($module, $name)=self::parseName($name);
        $module=Application::getModuleFullName($module);
        $name=$module.':'.$name;
        _D()->debug($name);
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
            _D()->warning(__('get url for %s failed,module:%s args:%s', $name, $module, json_encode($values)));
            return '#the-router-['.$name.']-is-undefined--please-check-out-router-list';
        }
        if (count($values)) {
            return Request::getInstance()->baseUrl(). ltrim($url, '/').'?'.http_build_query($values, 'v', '&', PHP_QUERY_RFC3986);
        }
        return Request::getInstance()->baseUrl(). ltrim($url, '/');
    }


    public function dispatch()
    {
        _D()->time('dispatch');
        self::buildRouterMap();
        // Hook前置路由（自定义过滤器|自定义路由）
        if (Hook::execIf('Router:dispatch::before', [Request::getInstance()], true)) {
            if (($router_name=self::matchRouterMap())!==false) {
                _D()->debug('dispatch match '.$router_name);
                _D()->timeEnd('dispatch');
                Response::setName($router_name);
                _D()->time('run router');
                self::runRouter($this->routers[$router_name]);
                _D()->timeEnd('run router');
            } else {
                Hook::exec('system:404');
            }
        } else {
            Hook::execTail('Router:dispatch::error');
        }
    }
    protected static function runRouter(array $router)
    {
        // _D()->time('active Module');
        (new \suda\tool\Command(Config::get('app.application', 'suda\\core\\Application').'::activeModule'))->exec([$router['module']]);
        // _D()->timeEnd('active Module');
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
                $this->page('suda:error404', ['title'=>'404 Error', 'path'=>$request->url()])->render();
            }
        };
        $render->onRequest(Request::getInstance());
    }
}
