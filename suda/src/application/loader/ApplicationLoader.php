<?php

namespace suda\application\loader;

use suda\framework\Config;
use suda\application\Module;
use suda\application\Application;
use suda\application\ApplicationModule;

/**
 * 应用程序
 */
class ApplicationLoader extends ApplicationBaseLoader
{

    const CACHE_ROUTE = 'application-route';
    const CACHE_ROUTE_RUNNABLE = 'application-route-runnable';

    /**
     * @var Application
     */
    protected $application;

    /**
     * ApplicationLoader constructor.
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        parent::__construct($application);
    }

    /**
     * 加载路由
     */
    public function loadRoute()
    {
        $name = 'application-route';
        $this->application->debug()->time($name);
        if (static::isDebug()) {
            $this->loadRouteFromModules();
            if ($this->application->getRoute()->isContainClosure()) {
                $this->application->debug()->warning('route contain closure, route prepare cannot be cacheables');
            } else {
                $this->application->cache()->set(ApplicationLoader::CACHE_ROUTE, $this->application->getRoute()->getRouteCollection());
                $this->application->cache()->set(ApplicationLoader::CACHE_ROUTE_RUNNABLE, $this->application->getRoute()->getRunnable());
            }
        } elseif ($this->routeCacheAvailable()) {
            $route = $this->application->cache()->get(ApplicationLoader::CACHE_ROUTE);
            $runnable = $this->application->cache()->get(ApplicationLoader::CACHE_ROUTE_RUNNABLE);
            $this->application->getRoute()->setRouteCollection($route);
            $this->application->getRoute()->setRunnable($runnable);
            $this->application->debug()->info('load route from cache');
        } else {
            $this->loadRouteFromModules();
        }
        $this->application->debug()->timeEnd($name);
    }

    /**
     * 从模块中加载路由
     */
    private function loadRouteFromModules()
    {
        foreach ($this->application->getModules() as $name => $module) {
            if ($module->getStatus() === Module::REACHABLE) {
                $this->loadModuleRoute($module);
                $this->application->debug()->debug('reachable # ' . $module->getFullName());
            }
        }
    }

    /**
     * @return bool
     */
    private function routeCacheAvailable()
    {
        return $this->application->cache()->has(ApplicationLoader::CACHE_ROUTE)
            && $this->application->cache()->has(ApplicationLoader::CACHE_ROUTE_RUNNABLE);
    }

    /**
     * 加载路由
     * @param Module $module
     */
    protected function loadModuleRoute(Module $module)
    {
        foreach ($this->application->getRouteGroup() as $group) {
            $this->loadRouteGroup($module, $group);
        }
    }

    /**
     * 加载路由组
     *
     * @param Module $module
     * @param string $groupName
     * @return void
     */
    protected function loadRouteGroup(Module $module, string $groupName)
    {
        $group = $groupName === 'default' ? '' : '-' . $groupName;
        if ($path = $module->getResource()->getConfigResourcePath('config/route' . $group)) {
            $routeConfig = Config::loadConfig($path, [
                'module' => $module->getName(),
                'group' => $groupName,
                'property' => $module->getProperty(),
                'config' => $module->getConfig(),
            ]);
            if ($routeConfig !== null) {
                $prefix = $module->getConfig('route-prefix.' . $groupName, '');
                $this->loadRouteConfig($module, $prefix, $groupName, $routeConfig);
            }
        }
    }

    /**
     * 加载模块路由配置
     *
     * @param Module $module
     * @param string $prefix
     * @param string $groupName
     * @param array $routeConfig
     * @return void
     */
    protected function loadRouteConfig(Module $module, string $prefix, string $groupName, array $routeConfig)
    {
        $module = $module->getFullName();
        foreach ($routeConfig as $name => $config) {
            $exname = $this->application->getRouteName($name, $module, $groupName);
            $method = $config['method'] ?? [];
            $attributes = [];
            $attributes['module'] = $module;
            $attributes['config'] = $config;
            $attributes['group'] = $groupName;
            $attributes['route'] = $exname;
            $uri = $config['uri'] ?? '/';
            $anti = array_key_exists('anti-prefix', $config) && $config['anti-prefix'];
            if ($anti) {
                $uri = '/' . trim($uri, '/');
            } else {
                $uri = '/' . trim($prefix . $uri, '/');
            }
            $this->application->request($method, $exname, $uri, $attributes);
        }
    }
}
