<?php

namespace suda\application\loader;

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

    public function load()
    {
        $this->loadVendorIfExist();
        $this->loadGlobalConfig();
        $this->registerModule();
        $this->setModuleStatus();
        $this->prepareModule();
        $this->activeModule();
    }


    public function loadRoute()
    {
        foreach ($this->application->getModules() as $name => $module) {
            if ($module->getStatus() === Module::REACHABLE) {
                call_user_func([$this->moduleLoader[$name], 'toReachable']);
            }
        }
    }


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

    public function loadVendorIfExist()
    {
        $vendorAutoload = $this->application->getPath() . '/vendor/autoload.php';
        if (FileSystem::exist($vendorAutoload)) {
            Loader::requireOnce($vendorAutoload);
        }
    }

    /**
     * @throws SQLException
     */
    public function loadDataSource()
    {
        Database::loadApplication($this->application);
        $dataSource = Database::getDefaultDataSource();
        $this->application->setDataSource($dataSource);
    }


    protected function prepareModule()
    {
        foreach ($this->application->getModules()->all() as $name => $module) {
            $this->moduleLoader[$name] = new ModuleLoader($this->application, $module);
            $this->moduleLoader[$name]->toLoad();
            $this->moduleLoader[$name]->loadExtraModuleResourceLibrary();
        }
    }

    protected function activeModule()
    {
        foreach ($this->application->getModules()->all() as $name => $module) {
            if ($module->getStatus() !== Module::LOADED) {
                $this->moduleLoader[$name]->toActive();
            }
        }
    }

    protected function registerModule()
    {
        $extractPath = $this->application->getDataPath() . '/extract-module';
        FileSystem::make($extractPath);
        foreach ($this->application->getModulePaths() as $path) {
            $this->registerModuleFrom($path, $extractPath);
        }
    }

    protected function setModuleStatus()
    {
        $active = $this->application->getManifest('module.active', $this->actionableModules);
        $reachable = $this->application->getManifest('module.reachable', $this->reachableModules);
        $this->setModuleActive($this->application->getModules(), $active);
        $this->setModuleReachable($this->application->getModules(), $reachable);
    }

    protected function registerModuleFrom(string $path, string $extractPath)
    {
        $modules = new ModuleBag;
        foreach (ModuleBuilder::scan($path, $extractPath) as $module) {
            $modules->add($module);
        }
        $this->prepareModuleConfig($path, $modules);
    }

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
