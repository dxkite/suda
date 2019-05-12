<?php
namespace suda\application\loader;

use function strtolower;
use suda\orm\DataSource;
use suda\framework\Config;
use suda\application\Module;
use suda\application\Resource;
use suda\application\ModuleBag;
use suda\application\Application;
use suda\framework\filesystem\FileSystem;
use suda\orm\connection\observer\Observer;
use suda\application\builder\ModuleBuilder;
use suda\application\database\DebugObserver;
use suda\orm\exception\SQLException;

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
        $this->prepareModuleLoader();
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
        $dataSource = $this->getDataSourceGroup('default');
        $this->application->setDataSource($dataSource);
    }

    /**
     * @param string $groupName
     * @return DataSource
     * @throws SQLException
     */
    public function getDataSourceGroup(string $groupName):DataSource
    {
        $group = $groupName === 'default' ? '': '-'. $groupName;
        $dataSourceConfigPath = $this->application->getResource()->getConfigResourcePath('config/data-source'.$group);
        $dataSource = new DataSource;
        $observer = new DebugObserver($this->application->debug());
        if ($dataSourceConfigPath !== null) {
            $dataSourceConfig = Config::loadConfig($dataSourceConfigPath);
            foreach ($dataSourceConfig as $name => $config) {
                $this->addDataSource(
                    $dataSource,
                    $observer,
                    $name,
                    $config['type'] ?? 'mysql',
                    $config['mode'] ?? '',
                    $config
                );
            }
        }
        return $dataSource;
    }

    /**
     * @param DataSource $source
     * @param Observer $observer
     * @param string $name
     * @param string $type
     * @param string $mode
     * @param array $config
     * @throws SQLException
     */
    protected function addDataSource(
        DataSource $source,
        Observer $observer,
        string $name,
        string $type,
        string $mode,
        array $config
    ) {
        $mode = strtolower($mode);
        $data = DataSource::new($type, $config, $name);
        $data->setObserver($observer);
        if (strlen($mode) > 0) {
            if (strpos($mode, 'read') !== false || strpos($mode, 'slave') !== false) {
                $source->addRead($data);
            }
            if (strpos($mode, 'write') !== false) {
                $source->addWrite($data);
            }
            if (strpos($mode, 'master') !== false) {
                $source->add($data);
            }
        } else {
            $source->add($data);
        }
    }

    protected function prepareModuleLoader()
    {
        foreach ($this->application->getModules()->all() as $name => $module) {
            $this->moduleLoader[$name] = new ModuleLoader($this->application, $module);
            $this->moduleLoader[$name]->toLoad();
            if ($module->getStatus() !== Module::LOADED) {
                $this->moduleLoader[$name]->toActive();
            }
        }
    }

    protected function registerModule()
    {
        $extractPath = SUDA_DATA .'/extract-module';
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
