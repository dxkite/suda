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


    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    public function load()
    {
        $this->loadVendorIfExist();
        $this->loadGlobalConfig();
        $this->registerModule();
        $this->prepareModule();
        $this->activeModule();
    }


    public function loadRoute()
    {
        foreach ($this->application->getModules() as $name => $module) {
            if ($module->getStatus() === Module::REACHABLE) {
                call_user_func([$this->moduleLoader[$name],'toReachable']);
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
        $vendorAutoload = $this->application->getPath().'/vendor/autoload.php';
        if (FileSystem::exist($vendorAutoload)) {
            require_once $vendorAutoload;
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
        $extractPath = $this->application->getDataPath() .'/extract-module';
        FileSystem::make($extractPath);
        foreach ($this->application->getModulePaths() as $path) {
            $this->registerModuleFrom($path, $extractPath);
        }
    }

    protected function registerModuleFrom(string $path, string $extractPath)
    {
        $modules = new ModuleBag;
        foreach (ModuleBuilder::scan($path, $extractPath) as $module) {
            $modules->add($module);
        }
        $this->assignModuleToApplication($path, $modules);
    }

    protected function assignModuleToApplication(string $path, ModuleBag $modules)
    {
        $resource = new Resource([$path]);
        $configPath = $resource->getConfigResourcePath('config');
        $config = null;
        if ($configPath) {
            $config = Config::loadConfig($configPath, $this->application->getConfig());
        }
        $config = $config ?? [];
        $moduleNames = array_keys($modules->all());
        $load  = $config['load'] ?? $moduleNames;
        $this->loadModules($modules, $load);
        $active = $config['active'] ?? $load;
        $this->setModuleActive($modules, $active);
        $this->setModuleReachable($modules, $config['reachable'] ?? $active);
    }

    protected function loadModules(ModuleBag $bag, array $load)
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
