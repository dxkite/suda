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
    protected static $router=null;
    protected $routers=[];
    const CACHE_NAME='route.mapping';
    protected static $cacheName=null;
    protected static $cacheModules=null;
    protected static $groups=null;

    private function __construct()
    {
        Hook::listen('suda:system:error::404', [$this,'error']);
        Hook::listen('suda:route:dispatch::error', [$this,'error']);
    }

    public static function getInstance()
    {
        if (is_null(self::$router)) {
            self::$router=new Router;
        }
        return self::$router;
    }
    
    public static function getModulePrefix(string $module,string $group)
    {
        return Application::getInstance()->getModulePrefix($module,$group);
    }

    public function load(string $module)
    {
        debug()->trace(__('load module router: $0', $module));
        $groups = $this->getRouterGroups();
        foreach ($groups as $index => $name) {
            $group = trim(is_numeric($index)?$name:$index);
            $config = $group === Mapping::DEFAULT_GROUP ? 'router': 'router-'.$name;
            if ($file=Application::getInstance()->getModuleConfigPath($module, $config)) {
                $loadedRouters= self::loadModuleRouteConfig($group, $module, $file);
                debug()->trace(__('loading $1 route from file $0', $file, $group));
                $this->routers=array_merge($this->routers, $loadedRouters);
            }
        }
    }
    
    public function getRouterGroups():array
    {
        if (is_null(self::$groups)) {
            $groups = conf('app.router.groups', [Mapping::DEFAULT_GROUP]);
            if (defined('ROUTER_GROUPS')) {
                $groups = explode(',', ROUTER_GROUPS);
            }
            self::$groups = $groups;
        }
        return self::$groups;
    }

    protected function loadModuleRouteConfig(string $group, string $module, string $configFile)
    {
        $routers=Config::loadConfig($configFile, $module);
        $router=[];
        foreach ($routers as $name => $value) {
            $mapping=Mapping::createFromRouteArray($group, $module, $name, $value);
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
        storage()->put($this->cacheFile('.modules'), implode(PHP_EOL, self::$cacheModules));
        storage()->put($this->cacheFile(self::CACHE_NAME), serialize($this->routers));
        if (DEBUG) {
            storage()->put($this->cacheFile(self::CACHE_NAME.'.json'), json_encode($this->routers, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
        }
    }

    public function loadModulesRouter()
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
        Hook::exec('suda:route:load', [$this]);
    }

    public function routerCached()
    {
        if (!storage()->isWritable(CACHE_DIR)) {
            return false;
        }
        if (!file_exists($this->cacheFile(self::CACHE_NAME))) {
            return false;
        }
        return true;
    }

    public function prepareRouterInfo()
    {
        $modules=Application::getInstance()->getReachableModules();
        foreach ($modules as $module) {
            self::load($module);
        }
        Hook::exec('suda:route:prepare', [$this]);
        // 缓存路由信息
        if (storage()->isWritable(CACHE_DIR)) {
            self::saveFile();
        }
    }


    public function parseUrl(string $url):?Mapping
    {
        $paramValue=[];
        $info=parse_url($url);
        if (isset($info['query'])) {
            parse_str($info['query'], $paramValue);
        }
        if (isset($info['host'])) {
            if ($info['host']!= Request::getHost() &&  $info['host']!='localhost') {
                return null;
            }
        }

        list($url, $queryString)=Request::parseUrl($info['path']);
        
        if ($queryString) {
            parse_str($queryString, $paramValue);
        }
        $ignoreCase=conf('app.url.ignore-case', true);
        $target=null;
        foreach ($this->routers as $name => $mapping) {
            if ($mapping->matchUrlValue($url, $ignoreCase, $paramValue)) {
                $target=$mapping;
                $target->setValue($paramValue);
                break;
            }
        }
        return $target;
    }

    protected function matchRouterMap()
    {
        $request=Request::getInstance();
        $ignoreCase=conf('app.url.ignore-case', true);
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
    public static function parseName(string $name, ?string $moduleDefault=null)
    {
        if (is_null($moduleDefault)) {
            $moduleDefault=Application::getInstance()->getActiveModule();
        }
        // [模块前缀名称/]模块名[:版本号]:(模板名|路由ID)
        if (preg_match('/^((?:[a-zA-Z0-9_\-.]+\/)?[a-zA-Z0-9_\-.]+)(?::([^:]+))?(?::(.+))?$/', $name, $match)) {
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

    public function getRouterFullName(string $name, ?string $moduleDefault=null)
    {
        list($module, $name)=self::parseName($name, $moduleDefault);
        $module=Application::getInstance()->getModuleFullName($module);
        return $module.':'.$name;
    }
    
    public function buildUrlArgs(string $name, array $args, ?string $moduleDefault =null)
    {
        list($module, $name)=self::parseName($name, $moduleDefault);
        $module=Application::getInstance()->getModuleFullName($module);
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

    /**
     * 将 router:// 协议指定的URL转换为 URL
     *
     * @param string $uri
     * @return string|null
     */
    public function decode(string $uri):?string
    {
        $values=parse_url($uri);
        $type = $values['scheme'];
        $host = $values['host'];
        $name = trim($values['path']??'', '/');
        parse_str($values['query'] ?? '', $params);
        if ($type == 'router') {
            list($module, $name)=self::parseName($name);
            $module=app()->getModuleFullName($module);
            $name=$module.':'.$name;
            if (isset($this->routers[$name])) {
                $router=clone $this->routers[$name];
                $router->setHost($host);
                return $router->createUrl($params);
            }
        }
        return null;
    }


    /**
     * 将URL转换为 router:// 协议形式
     *
     * @param string $url
     * @param boolean $fullmodule
     * @return string|null
     */
    public function encode(string $url, bool $fullmodule=false):?string
    {
        $mapping = $this->parseUrl($url);
        if ($mapping) {
            $router = $fullmodule?$mapping->getFullName():$mapping->getSortName();
            $uri = 'router://'.$mapping->getHost().'/'. $router ;
            $value = $mapping->getValue();
            if (is_array($value) && count($value)) {
                $uri .='?'. http_build_query($value);
            }
            return $uri;
        }
        return null;
    }
    
    /**
     * 根据路由名称创建URL
     *
     * @param string $name 路由名称
     * @param array $values 路由中的参数
     * @param boolean $query 是否使用多余路由参数作为查询参数
     * @param array $queryArr 查询参数
     * @param string|null $moduleDefault 路由未指定模块时的默认模块
     * @return string
     */
    public function buildUrl(string $name, array $values=[], bool $query=true, array $queryArr=[], ?string $moduleDefault =null):string
    {
        list($module, $name)=self::parseName($name, $moduleDefault);
        $module=Application::getInstance()->getModuleFullName($module);
        $name=$module.':'.$name;
        if (isset($this->routers[$name])) {
            return $this->routers[$name]->createUrl($values, $query, $queryArr);
        } else {
            debug()->warning(__('get url for $0 failed,module:$1 args:$2', $name, $module, json_encode($values)));
            return '#the-router-['.$name.']-is-undefined--please-check-out-router-list';
        }
    }

    public function dispatch()
    {
        debug()->time('dispatch');
        if (Hook::execIf('suda:route:dispatch::before', [Request::getInstance()], true)) {
            if (($mapping=self::matchRouterMap())!==false) {
                debug()->timeEnd('dispatch');
                Response::setName($mapping->getFullName());
                debug()->time('run router');
                $this->runRouter($mapping);
                debug()->timeEnd('run router');
            } else {
                if (!Hook::execIf('suda:route:dispatch::extra', [Request::getInstance()], true)) {
                    Hook::execTail('suda:system:error::404');
                }
            }
        } else {
            Hook::execTail('suda:route:dispatch::error');
        }
    }

    /**
     * 获取路由
     *
     * @param string $name
     * @param string|null $moduleDefault
     * @return Mapping|null
     */
    public function getRouter(string $name, ?string $moduleDefault=null):?Mapping
    {
        $name=self::getRouterFullName($name, $moduleDefault);
        if (isset($this->routers[$name])) {
            return $this->routers[$name];
        }
        return null;
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
        Hook::exec('suda:route:run::before', [&$mapping]);
        // debug()->time('active Module');
        // 激活模块
        System::getAppInstance()->activeModule($mapping->getModule());
        // debug()->timeEnd('active Module');
        debug()->time('request');
        // 运行请求
        $mapping->run();
        debug()->timeEnd('request');
        // 请求结束
        Hook::exec('suda:route:run::after', [&$mapping]);
    }


    public static function error(int $code=404)
    {
        $render=new class extends Response {
            public function onRequest(Request $request)
            {
                $this->state($this->code);
                $view=$this->view('suda:error/'.$this->code);
                if (!$view) {
                    $view=$this->page('suda:http_error');
                }
                $view->assign(['error_type'=>'Error','error_code'=>$this->code,'error_message'=> Response::statusMessage($this->code)]);
                if ($this->code == 404) {
                    $view->set('path', $request->url());
                }
                $view->render();
            }
        };
        $render->code = $code;
        $render->onRequest(Request::getInstance());
        return true;
    }

    public function getRouters()
    {
        return $this->routers;
    }

    private function cacheFile(string $name):string
    {
        if (is_null(self::$cacheName)) {
            $reachable = app()->getReachableModules();
            $groups = $this->getRouterGroups();
            sort($groups);
            sort($reachable);
            self::$cacheModules = $reachable;
            self::$cacheName = implode('-',$groups).'/'.substr(md5(implode('-', $reachable)), 0, 8);
        }

        $path = CACHE_DIR.'/router/'.self::$cacheName;
        $path = Storage::path($path);
        return $path.DIRECTORY_SEPARATOR.$name;
    }
}
