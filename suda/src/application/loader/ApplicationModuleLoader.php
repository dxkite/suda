<?php


namespace suda\application\loader;

use suda\framework\Config;
use suda\application\Module;
use suda\application\Resource;
use suda\application\ModuleBag;
use suda\application\Application;
use suda\application\ApplicationModule;
use suda\framework\filesystem\FileSystem;
use suda\application\builder\ModuleBuilder;

/**
 * Class ApplicationModuleLoader
 * 应用模块加载器
 *
 * @package suda\application\loader
 */
class ApplicationModuleLoader
{
    const CACHE_MODULE = 'application-module';

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

    /**
     * ApplicationModuleLoader constructor.
     * @param ApplicationModule $application
     */
    public function __construct(ApplicationModule $application)
    {
        $this->application = $application;
        $this->actionableModules = [];
        $this->reachableModules = [];
    }

    /**
     * 加载模块
     */
    public function loadModule()
    {
        $this->loadModuleLocalOrCache();
        $this->prepareModule();
        $this->activeModule();
    }

    /**
     * 调试模式
     *
     * @return bool
     */
    public static function isDebug()
    {
        return boolval(defined('SUDA_DEBUG') ? constant('SUDA_DEBUG') : false);
    }

    /**
     * @param ModuleBag $bag
     * @param array $load
     */
    private function loadModuleFromBag(ModuleBag $bag, array $load)
    {
        foreach ($load as $moduleName) {
            if ($module = $bag->get($moduleName)) {
                $module->setStatus(Module::LOADED);
                $this->application->add($module);
            }
        }
    }

    /**
     * 获取目录的模板配置
     *
     * @param string $path
     * @return array
     */
    private function getModuleDirectoryConfig(string $path)
    {
        $resource = new Resource([$path]);
        $configPath = $resource->getConfigResourcePath('config');
        if ($configPath) {
            return Config::loadConfig($configPath, $this->application->getConfig()) ?? [];
        }
        return [];
    }

    /**
     * 准备模块
     */
    private function prepareModule()
    {
        foreach ($this->application->getModules() as $name => $module) {
            $this->moduleLoader[$name] = new ModuleLoader($this->application, $module);
            $this->moduleLoader[$name]->toLoad(); // 切换到加载状态
            $this->moduleLoader[$name]->loadExtraModuleResourceLibrary(); // 加载二外的模块源
        }
    }

    /**
     * 设置模块状态
     */
    private function setModuleStatus()
    {
        $active = $this->application->getManifest('module.active', $this->actionableModules);
        $reachable = $this->application->getManifest('module.reachable', $this->reachableModules);
        $this->setModuleActive($this->application->getModules(), $active);
        $this->setModuleReachable($this->application->getModules(), $reachable);
    }

    /**
     * @param ModuleBag $bag
     * @param array $active
     */
    private function setModuleActive(ModuleBag $bag, array $active)
    {
        foreach ($active as $moduleName) {
            if ($module = $bag->get($moduleName)) {
                $module->setStatus(Module::ACTIVE);
            }
        }
    }

    /**
     * 加载模块
     */
    private function loadModuleLocalOrCache()
    {
        $name = ApplicationModuleLoader::CACHE_MODULE;
        $this->application->debug()->time($name);
        // 调试模式不缓存
        if (static::isDebug()) {
            $this->registerModule();
            $this->setModuleStatus();
            $this->application->cache()->set($name, $this->application->getModules());
        } elseif ($this->application->cache()->has($name)) {
            $module = $this->application->cache()->get($name);
            $this->application->setModule($module);
            $this->application->debug()->info('load modules from cache');
        } else {
            $this->registerModule();
            $this->setModuleStatus();
        }
        $this->application->debug()->timeEnd($name);
    }

    /**
     * 注册模块
     */
    private function registerModule()
    {
        $extractPath = $this->application->getDataPath() . '/extract-module';
        FileSystem::make($extractPath);
        foreach ($this->application->getModulePaths() as $path) {
            $this->registerModuleFrom($path, $extractPath);
        }
    }


    /**
     * 激活模块
     */
    private function activeModule()
    {
        foreach ($this->application->getModules() as $name => $module) {
            if ($module->getStatus() !== Module::LOADED) {
                $this->moduleLoader[$name]->toActive();
            }
        }
    }

    /**
     * 注册模块下的模块
     *
     * @param string $path
     * @param string $extractPath
     */
    private function registerModuleFrom(string $path, string $extractPath)
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
    private function prepareModuleConfig(string $path, ModuleBag $modules)
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
     * @param ModuleBag $bag
     * @param array $reachable
     */
    private function setModuleReachable(ModuleBag $bag, array $reachable)
    {
        foreach ($reachable as $moduleName) {
            if ($module = $bag->get($moduleName)) {
                $module->setStatus(Module::REACHABLE);
            }
        }
    }
}