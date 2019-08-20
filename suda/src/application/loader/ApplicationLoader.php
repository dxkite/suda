<?php

namespace suda\application\loader;

use suda\framework\Cache;
use suda\framework\Config;
use suda\application\Module;
use suda\application\Resource;
use suda\application\ModuleBag;
use suda\application\Application;
use suda\application\database\Database;
use suda\database\exception\SQLException;
use suda\framework\filesystem\FileSystem;
use suda\application\builder\ModuleBuilder;
use suda\framework\loader\Loader;
use suda\framework\runnable\Runnable;

/**
 * 应用程序
 */
class ApplicationLoader
{
    /**
     * 应用程序
     *
     * @var Application
     */
    protected $application;

    /**
     * 模块加载器
     *
     * @var ModuleLoader[]
     */
    protected $moduleLoader;


    /**
     * @var array
     */
    protected $actionableModules;

    /**
     * @var array
     */
    protected $reachableModules;

    public function __construct(Application $application)
    {
        $this->application = $application;
        $this->actionableModules = [];
        $this->reachableModules = [];
    }

    /**
     * 加载APP
     */
    public function load()
    {
        $this->loadVendorIfExist();
        $this->loadGlobalConfig();
        $this->loadModule();
        $this->prepareModule();
        $this->activeModule();
    }

    /**
     * 加载模块
     */
    protected function loadModule()
    {
        $this->application->debug()->time('prepare modules');
        // 调试模式不缓存
        if (static::isDebug()) {
            $this->registerModule();
            $this->setModuleStatus();
        } else {
            if ($this->application->cache()->has('application-module')) {
                $module = $this->application->cache()->get('application-module');
                $this->application->setModule($module);
                $this->application->debug()->info('load modules from cache');
            } else {
                $this->registerModule();
                $this->setModuleStatus();
                $this->application->cache()->set('application-module', $this->application->getModules());
            }
        }
        $this->application->debug()->timeEnd('prepare modules');
    }

    /**
     * 调试模式
     *
     * @return bool
     */
    public static function isDebug() {
        return boolval(defined('SUDA_DEBUG') ? constant('SUDA_DEBUG') : false);
    }

    /**
     * 加载路由
     */
    public function loadRoute()
    {
        foreach ($this->application->getModules() as $name => $module) {
            if ($module->getStatus() === Module::REACHABLE) {
                call_user_func([$this->moduleLoader[$name], 'toReachable']);
            }
        }
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
     * 准备数据源
     *
     * @throws SQLException
     */
    public function loadDataSource()
    {
        Database::loadApplication($this->application);
        $dataSource = Database::getDefaultDataSource();
        $this->application->setDataSource($dataSource);
    }

    /**
     * 准备模块
     */
    protected function prepareModule()
    {
        foreach ($this->application->getModules()->all() as $name => $module) {
            $this->moduleLoader[$name] = new ModuleLoader($this->application, $module);
            $this->moduleLoader[$name]->toLoad(); // 切换到加载状态
            $this->moduleLoader[$name]->loadExtraModuleResourceLibrary(); // 加载二外的模块源
        }
    }

    /**
     * 激活模块
     */
    protected function activeModule()
    {
        foreach ($this->application->getModules()->all() as $name => $module) {
            if ($module->getStatus() !== Module::LOADED) {
                $this->moduleLoader[$name]->toActive();
            }
        }
    }

    /**
     * 注册模块
     */
    protected function registerModule()
    {
        $extractPath = $this->application->getDataPath() . '/extract-module';
        FileSystem::make($extractPath);
        foreach ($this->application->getModulePaths() as $path) {
            $this->registerModuleFrom($path, $extractPath);
        }
    }

    /**
     * 设置模块状态
     */
    protected function setModuleStatus()
    {
        $active = $this->application->getManifest('module.active', $this->actionableModules);
        $reachable = $this->application->getManifest('module.reachable', $this->reachableModules);
        $this->setModuleActive($this->application->getModules(), $active);
        $this->setModuleReachable($this->application->getModules(), $reachable);
    }

    /**
     * 注册模块下的模块
     *
     * @param string $path
     * @param string $extractPath
     */
    protected function registerModuleFrom(string $path, string $extractPath)
    {
        $modules = new ModuleBag;
        foreach (ModuleBuilder::scan($path, $extractPath) as $module) {
            $modules->add($module);
        }
        $this->prepareModuleConfig($path, $modules);
    }

    /**
     * 获取模块的配置
     *
     * @param string $path
     * @param ModuleBag $modules
     */
    protected function prepareModuleConfig(string $path, ModuleBag $modules)
    {
        $config = $this->getModuleDirectoryConfig($path);
        $moduleNames = array_keys($modules->all());
        // 获取模块文件夹模块配置
        $load = $config['load'] ?? $moduleNames;
        $active = $config['active'] ?? $load;
        $reachable = $config['reachable'] ?? $active;
        // 获取允许加载的模块
        $load = $this->application->getManifest('module.load', $load);
        $this->loadModuleFromBag($modules, $load);
        $this->actionableModules = array_merge($this->actionableModules, $active);
        $this->reachableModules = array_merge($this->reachableModules, $reachable);
    }

    /**
     * 获取目录的模板配置
     *
     * @param string $path
     * @return array
     */
    protected function getModuleDirectoryConfig(string $path)
    {
        $resource = new Resource([$path]);
        $configPath = $resource->getConfigResourcePath('config');
        if ($configPath) {
            return Config::loadConfig($configPath, $this->application->getConfig()) ?? [];
        }
        return [];
    }

    protected function loadModuleFromBag(ModuleBag $bag, array $load)
    {
        foreach ($load as $moduleName) {
            if ($module = $bag->get($moduleName)) {
                $module->setStatus(Module::LOADED);
                $this->application->add($module);
            }
        }
    }

    protected function setModuleActive(ModuleBag $bag, array $active)
    {
        foreach ($active as $moduleName) {
            if ($module = $bag->get($moduleName)) {
                $module->setStatus(Module::ACTIVE);
            }
        }
    }

    protected function setModuleReachable(ModuleBag $bag, array $reachable)
    {
        foreach ($reachable as $moduleName) {
            if ($module = $bag->get($moduleName)) {
                $module->setStatus(Module::REACHABLE);
            }
        }
    }
}
