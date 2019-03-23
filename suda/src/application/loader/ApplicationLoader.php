<?php
namespace suda\application\loader;

use suda\orm\DataSource;
use suda\framework\Config;
use suda\framework\Context;
use suda\application\Module;
use suda\application\Resource;
use suda\application\ModuleBag;
use suda\application\Application;
use suda\application\loader\ModuleLoader;
use suda\framework\filesystem\FileSystem;
use suda\orm\connection\observer\Observer;
use suda\application\builder\ModuleBuilder;
use suda\framework\response\ContentWrapper;
use suda\application\database\DebugObserver;

/**
 * 应用程序
 */
class ApplicationLoader
{
    /**
     * 应用程序
     *
     * @var \suda\application\Application
     */
    protected $application;
    
    /**
     * 模块加载器
     *
     * @var \suda\application\loader\ModuleLoader[]
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
        $this->prepareModuleLoader();
    }

    public function loadRoute()
    {
        foreach ($this->application->getModule()->all() as $module) {
            if ($module->getStatus() === Module::REACHABLE) {
                $this->moduleLoader[$module->getFullName()]->toReacheable();
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

    public function loadDataSource()
    {
        $dataSourceConfigPath = $this->application->getResource()->getConfigResourcePath('config/data-source');
        $dataSource = new DataSource;
        $observer = new DebugObserver($this->application->debug());
        if ($dataSourceConfigPath !== null) {
            $dataSourceConfig = Config::loadConfig($dataSourceConfigPath);
            foreach ($dataSourceConfig as $name => $config) {
                $this->addDataSource($dataSource, $observer, $name, $config['type'] ?? 'mysql', $config['mode'] ?? 'master', $config);
            }
        }
        $this->application->setDataSource($dataSource);
    }

    protected function addDataSource(DataSource $source, Observer $observer, string $name, string $type, string $mode, array $config)
    {
        $mode = \strtolower($mode);
        $data = DataSource::new($type, $config, $name);
        $data->setObserver($observer);
        if (strpos($mode, 'read') !== false || strpos($mode, 'slave') !== false) {
            $source->addRead($data);
        } elseif (strpos($mode, 'write') !== false || strpos($mode, 'master') !== false) {
            $source->addWrite($data);
        } else {
            $source->add($data);
        }
    }

    protected function prepareModuleLoader()
    {
        foreach ($this->application->getModule()->all() as $module) {
            $this->moduleLoader[$module->getFullName()] = new ModuleLoader($this->application, $module);
            $this->moduleLoader[$module->getFullName()]->toLoaded();
        }
    }

    protected function registerModule()
    {
        $extractPath = SUDA_DATA .'/extract-module';
        FileSystem::make($extractPath);
        foreach ($this->application->getModulePaths() as  $path) {
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
        if ($config === null) {
            $this->application->getModule()->merge($modules);
        } else {
            $this->assignModuleWithStatusToApplication($modules, $config['loaded'] ?? [], $config['reachable'] ?? []);
        }
    }

    protected function assignModuleWithStatusToApplication(ModuleBag $modules, array $loaded, array $reachable)
    {
        foreach ($loaded as $moduleName) {
            if ($module = $modules->get($moduleName)) {
                $module->setStatus(Module::LOADED);
                $this->application->add($module);
            }
        }
        foreach ($reachable as $moduleName) {
            if ($module = $modules->get($moduleName)) {
                $module->setStatus(Module::REACHABLE);
            }
        }
    }
}
