<?php

namespace suda\application\loader;

use suda\framework\Cache;
use suda\framework\Config;
use suda\application\Module;
use suda\application\Resource;
use suda\application\ModuleBag;
use suda\application\Application;
use suda\framework\loader\Loader;
use suda\framework\runnable\Runnable;
use suda\application\database\Database;
use suda\database\exception\SQLException;
use suda\framework\filesystem\FileSystem;
use suda\application\builder\ModuleBuilder;

/**
 * 应用程序
 */
class ApplicationLoader extends ApplicationModuleLoader
{

    /**
     * 加载额外vendor
     */
    public function loadVendorIfExist()
    {
        $vendorAutoload = $this->application->getPath() . '/vendor/autoload.php';
        if (FileSystem::exist($vendorAutoload)) {
            Loader::requireOnce($vendorAutoload);
        }
    }

    /**
     * 加载APP
     */
    public function load()
    {
        $this->loadVendorIfExist();
        $this->loadGlobalConfig();
        $this->loadModule();
    }

    /**
     * 加载全局配置
     */
    public function loadGlobalConfig()
    {
        $resource = $this->application->getResource();
        if ($configPath = $resource->getConfigResourcePath('config/config')) {
            $this->application->getConfig()->load($configPath);
        }
        if ($listenerPath = $resource->getConfigResourcePath('config/listener')) {
            $this->application->loadEvent($listenerPath);
        }
    }

    /**
     * 加载路由
     */
    public function loadRoute()
    {
        $name = 'application-route';
        $this->application->debug()->time($name);
        if ($this->enableRouteCache() === false) {
            $this->loadRouteFromModules();
        } elseif ($this->application->cache()->has($name.'-route')) {
            $route = $this->application->cache()->get($name.'-route');
            $runnable = $this->application->cache()->get($name.'-runnable');
            $this->application->getRoute()->setRouteCollection($route);
            $this->application->getRoute()->setRunnable($runnable);
            $this->application->debug()->info('load route from cache');
        } else {
            $this->loadRouteFromModules();
            if ($this->application->getRoute()->isContainClosure()) {
                $this->application->debug()->warning('route contain closure, route prepare cannot be cacheables');
            } else {
                $this->application->cache()->set($name.'-route', $this->application->getRoute()->getRouteCollection());
                $this->application->cache()->set($name.'-runnable', $this->application->getRoute()->getRunnable());
            }
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
                call_user_func([$this->moduleLoader[$name], 'toReachable']);
            }
        }
    }

    /**
     * @return bool
     */
    private function enableRouteCache() {
        return boolval($this->application->getCache()->get('route-cache', static::isDebug()));
    }

    /**
     * 加载数据源
     *
     * @throws SQLException
     */
    public function loadDataSource()
    {
        Database::loadApplication($this->application);
        $dataSource = Database::getDefaultDataSource();
        $this->application->setDataSource($dataSource);
    }
}
