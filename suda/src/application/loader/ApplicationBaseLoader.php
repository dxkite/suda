<?php


namespace suda\application\loader;


use suda\framework\loader\Loader;
use suda\application\database\Database;
use suda\framework\filesystem\FileSystem;
use suda\database\exception\SQLException;

class ApplicationBaseLoader extends ApplicationModuleLoader
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
}