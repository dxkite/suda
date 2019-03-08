<?php
namespace suda\application\loader;

use suda\orm\DataSource;
use suda\framework\Config;
use suda\framework\Context;
use suda\application\Application;
use suda\application\loader\ModuleLoader;
use suda\framework\filesystem\FileSystem;
use suda\application\builder\ModuleBuilder;
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
        $this->registerModule();
        $this->prepareModuleLoader();
    }

    public function loadRoute()
    {
        $modules = $this->application->getManifast('reachable');
        foreach ($modules as $name) {
            $fullname = $this->application->find($name)->getFullName();
            $this->moduleLoader[$fullname]->toReacheable();
        }
    }

    public function loadDataSource()
    {
        $dataSourceConfigPath = $this->application->getResource()->getConfigResourcePath('config/data-source');
        $dataSource = new DataSource;
        $observer = new DebugObserver($this->application->getContext()->get('debug'));
        if ($dataSourceConfigPath !== null) {
            $dataSourceConfig = Config::loadConfig($dataSourceConfigPath);
            foreach ($dataSourceConfig as $name => $config) {
                $this->addDataSource($dataSource, $name, $config['type'] ?? 'mysql', $config['mode'] ?? 'master', $config);
            }
        }
        $this->application->setDataSource($dataSource);
        $this->application->getContext()->set('data-source', $dataSource);
        
    }

    protected function addDataSource(DataSource $source,Observer $observer, string $name,  string $type, string $mode, array $config)
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
        $modules = $this->application->getManifast('modules');
        foreach ($modules as $moduleName) {
            if ($module = $this->application->find($moduleName)) {
                $this->moduleLoader[$module->getFullName()] = new ModuleLoader($this->application, $module);
                $this->moduleLoader[$module->getFullName()]->toLoaded();
            }
        }
    }

    protected function registerModule()
    {
        $extractPath = FileSystem::makes(SUDA_DATA .'/extract-module');
        foreach ($this->application->getModulePaths() as  $path) {
            $this->registerModuleFrom($path, $extractPath);
        }
    }

    protected function registerModuleFrom(string $path, string $extractPath)
    {
        foreach (ModuleBuilder::scan($path, $extractPath) as $module) {
            $this->application->add($module);
        }
    }
}
