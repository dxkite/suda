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
use suda\core\route\Mapping;

/**
 * 路由处理类
 * 用于处理访问的路由信息
 */
class Router
{
    protected static $urltype=['int'=>'\d+','string'=>'[^\/]+','url'=>'.+'];
    protected static $router=null;
    protected $routers=[];
    const CACHE_NAME='route.mapping';
    protected static $cacheName=null;
    protected static $cacheModules=null;

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
    
    public static function getModulePrefix(string $module)
    {
        $prefix= Application::getInstance()->getInstance()->getModulePrefix($module)??'';
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
        return [$admin_prefix,$prefix];
    }

    public function load(string $module)
    {
        $simple_routers=[];
        $admin_routers=[];
        $module_path=Application::getInstance()->getInstance()->getModulePath($module);
        debug()->trace(__('load module:%s path:%s', $module, $module_path));
        // 加载前台路由
        if (Storage::exist($file=$module_path.'/resource/config/router.json')) {
            $simple_routers= self::loadModuleJson(Mapping::ROLE_SIMPLE, $module, $file);
            debug()->trace(__('loading simple route from file %s', $file));
        }
        // 加载后台路由
        if (Storage::exist($file=$module_path.'/resource/config/router_admin.json')) {
            $admin_routers= self::loadModuleJson(Mapping::ROLE_ADMIN, $module, $file);
            debug()->trace(__('loading admin route from file  %s', $file));
        }
        $this->routers=array_merge($this->routers, $admin_routers, $simple_routers);
    }

    protected function loadModuleJson(int $role, string $module, string $jsonfile)
    {
        $routers=Json::loadFile($jsonfile);
        $router=[];
        foreach ($routers as $name => $value) {
            $mapping=Mapping::createFromRouteArray($role, $module, $name, $value);
            if (!$mapping->isHidden()) {
                $router[$mapping->getFullName()]=$mapping;
            }
        }
        return $router;
    }

    protected function loadFile()
    {
        $routers=storage()->get($this->cacheFile(self::CACHE_NAME));
        $this->routers=unserialize($routers);
    }

    protected function saveFile()
    {
        storage()->put($this->cacheFile('.modules'), implode("\r\n", self::$cacheModules));
        storage()->put($this->cacheFile(self::CACHE_NAME), serialize($this->routers));
    }

    protected function loadModulesRouter()
    {
        // 如果DEBUG模式
        if (conf('debug', false)) {
            self::prepareRouterInfo();
        } else {
            if (self::routerCached()) {
                self::loadFile();
            } else {
                self::prepareRouterInfo();
            }
        }
    }


    public function routerCached()
    {
        if (!file_exists($this->cacheFile(self::CACHE_NAME))) {
            return false;
        }
    }

    public function prepareRouterInfo()
    {
        $modules=Application::getInstance()->getInstance()->getLiveModules();
        foreach ($modules as $module) {
            self::load($module);
        }
        Hook::exec('Router:prepareRouterInfo', [$this]);
        // 缓存路由信息
        self::saveFile();
    }


    public function parseUrl(string $url)
    {
        $paramValue=[];
        $info=parse_url($url);
        if (isset($info['query'])) {
            parse_str($info['query'], $paramValue);
        }
        if (isset($info['host'])) {
            if ($info['host']!=$_SERVER['HTTP_HOST'] &&  $info['host']!='localhost') {
                return false;
            }
        }

        list($url, $queryString)=Request::parseUrl($info['path']);
        
        if ($queryString) {
            parse_str($queryString, $paramValue);
        }
        $ignoreCase=conf('app.url.ignoreCase', true);
        $target=null;
        foreach ($this->routers as $name => $mapping) {
            if ($mapping->matchUrlValue($url, $ignoreCase, $paramValue)) {
                $target=$mapping;
                $target->setValue($paramValue);
                break;
            }
        }
        if ($target) {
            return $target;
        }
        return false;
    }

    protected function matchRouterMap()
    {
        $request=Request::getInstance();
        $ignoreCase=conf('app.url.ignoreCase', true);
        foreach ($this->routers as $name => $mapping) {
            if ($mapping->match($request, $ignoreCase)) {
                return $mapping;
            }
        }
        return false;
    }
    
    /**
     * 解析模板名
     *
     * @param string $name
     * @param string $moduleDefault
     * @return list(module,name)
     */
    public static function parseName(string $name, string $moduleDefault=null)
    {
        if (func_num_args()==2&&empty($moduleDefault)) {
            $moduleDefault=null;
        } elseif (empty($moduleDefault)) {
            $moduleDefault=Application::getInstance()->getInstance()->getActiveModule();
        }
        // [模块前缀名称/]模块名[:版本号]:(模板名|路由ID)
        if (preg_match('/^((?:[a-zA-Z0-9_-]+\/)?[a-zA-Z0-9_-]+)(?::([^:]+))?(?::(.+))?$/', $name, $match)) {
            if (isset($match[1]) && count($match)==2) {
                // 单纯路由或者模板
                $module=$moduleDefault;
                $info=$match[0];
            } else {
                $info=isset($match[3])?$match[3]:$match[2];
                $module=isset($match[3])?
                                (
                                    isset($match[1])?
                                    $match[1].(
                                        $match[2]?
                                        ':'.$match[2]
                                        :''
                                    )
                                    :$moduleDefault // 未指定模板名
                                )
                            :$match[1];
            }
        } else {
            $module=$moduleDefault;
            $info=$name;
        }
        return [$module,$info];
    }

    public function getRouterFullName(string $name)
    {
        list($module, $name)=self::parseName($name);
        $module=Application::getInstance()->getInstance()->getModuleFullName($module);
        return $module.':'.$name;
    }
    
    public function buildUrlArgs(string $name, array $args)
    {
        list($module, $name)=self::parseName($name);
        $module=Application::getInstance()->getInstance()->getModuleFullName($module);
        $name=$module.':'.$name;
        if (isset($this->routers[$name])) {
            $types=$this->routers[$name]->getTypes();
            if ($types) {
                $keys=array_keys($types);
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
        }
        return [];
    }

    public function buildUrl(string $name, array $values=[], bool $query=true, array $queryArr=[])
    {
        list($module, $name)=self::parseName($name);
        $module=Application::getInstance()->getInstance()->getModuleFullName($module);
        $name=$module.':'.$name;
        if (isset($this->routers[$name])) {
            return $this->routers[$name]->createUrl($values, $query, $queryArr);
        } else {
            debug()->warning(__('get url for %s failed,module:%s args:%s', $name, $module, json_encode($values)));
            return '#the-router-['.$name.']-is-undefined--please-check-out-router-list';
        }
    }

    public function dispatch()
    {
        debug()->time('dispatch');
        if (Hook::execIf('Router:dispatch::before', [Request::getInstance()], true)) {
            if (($mapping=self::matchRouterMap())!==false) {
                debug()->timeEnd('dispatch');
                Response::setName($mapping->getFullName());
                debug()->time('run router');
                $this->runRouter($mapping);
                debug()->timeEnd('run router');
            } else {
                Hook::execTail('system:404');
            }
        } else {
            Hook::execTail('Router:dispatch::error');
        }
    }

    /**
     * 获取路由
     *
     * @param string $name
     * @return void
     */
    public function getRouter(string $name)
    {
        $name=self::getRouterFullName($name);
        if (isset($this->routers[$name])) {
            return $this->routers[$name];
        }
    }
    
    /**
     * 设置路由别名
     *
     * @param string $name
     * @param string $alias
     * @return void
     */
    public function setRouterAlias(string $name, string $alias)
    {
        $name=self::getRouterFullName($name);
        $alias=self::getRouterFullName($alias);
        if (isset($this->routers[$name])) {
            $this->routers[$alias]=$this->routers[$name];
        }
    }
    
    /**
     * 路由替换
     *
     * @param string $name
     * @param string $alias
     * @return void
     */
    public function routerReplace(string $name, string $alias)
    {
        $name=self::getRouterFullName($name);
        $alias=self::getRouterFullName($alias);
        if (isset($this->routers[$name])) {
            if (isset($this->routers[$alias])) {
                $this->routers[$name]=$this->routers[$alias];
            }
        }
    }

    /**
     * 路由移动
     *
     * @param string $name
     * @param string $alias
     * @return void
     */
    public function routerMove(string $name, string $alias)
    {
        $this->routerReplace($name, $alias);
        $alias=self::getRouterFullName($alias);
        unset($this->router[$alias]);
    }
    

    public function addMapping(Mapping $mapping)
    {
        $this->routers[$mapping->getFullName()]=$mapping;
        $mapping->build();
        return $this;
    }

    public function refreshMapping(Mapping $mapping)
    {
        $name= $mapping->getFullName();
        if (isset($this->routers[$name])) {
            $this->routers[$name]=$mapping;
            return $this->routers[$name]->build();
        }
    }

    /**
     * 动态添加运行命令
     *
     * @param string $name
     * @param string $url
     * @param string $class
     * @param string $module
     * @param array $method
     * @return void
     */
    public function addRouter(string $name, string $url, string $class, string $module, array $method=[], bool $autoPrefix=false)
    {
        $mapping=new Mapping($name, $url, $class.'->onRequest', $module, $method);
        $fillName=$mapping->getFullName();
        $this->routers[$fillName]=$mapping;
        $mapping->setAntiPrefix(!$autoPrefix);
        $mapping->setDynamic();
        $mapping->build();
        return $fillName;
    }

    /**
     * 替换匹配表达式
     *
     * @param string $name
     * @param string $url
     * @param bool $preg
     * @return void
     */
    public function replaceMatch(string $name, string $url, bool $preg=false)
    {
        $name=self::getRouterFullName($name);
        if (isset($this->routers[$name])) {
            if ($preg) {
                return $this->routers[$name]->setMapping($url);
            }
            return $this->routers[$name]->setUrl($url)->build();
        }
    }

    /**
     * 替换路由指定类
     *
     * @param string $name
     * @param string $class
     * @param array $method
     * @return void
     */
    public function replaceClass(string $name, string $class, string $module=null, array $method=null)
    {
        $name=self::getRouterFullName($name);
        if (isset($this->routers[$name])) {
            $this->routers[$name]->setCallback($class.'->onRequest');
            if ($module) {
                $this->routers[$name]->setModule($module);
            }
            if ($method) {
                $this->routers[$name]->setMethod($method);
            }
            return $this->routers[$name];
        }
    }

    protected static function runRouter(Mapping $mapping)
    {
        // 全局钩子:重置Hook指向
        Hook::exec('Router:runRouter::before', [&$mapping]);
        // debug()->time('active Module');
        // 激活模块
        System::getAppInstance()->activeModule($mapping->getModule());
        // debug()->timeEnd('active Module');
        debug()->time('request');
        // 运行请求
        $mapping->run();
        debug()->timeEnd('request');
        // 请求结束
        Hook::exec('Router:runRouter::after', [&$mapping]);
    }

    public static function error404()
    {
        $render=new class extends Response {
            public function onRequest(Request $request)
            {
                $this->state(404);
                $this->page('suda:error404', ['title'=>'404 Error', 'path'=>$request->url()])->render();
            }
        };
        $render->onRequest(Request::getInstance());
    }

    public function getRouters()
    {
        return $this->routers;
    }

    private function cacheFile(string $name):string
    {
        if (is_null(self::$cacheName)) {
            $module_use=app()->getLiveModules();
            sort($module_use);
            self::$cacheModules=$module_use;
            self::$cacheName=substr(md5(implode('-', $module_use)), 0, 8);
        }
        $path=CACHE_DIR.'/router/'.self::$cacheName;
        Storage::path($path);
        return $path.'/'.$name;
    }
}
