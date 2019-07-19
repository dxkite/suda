<?php


namespace suda\application\database;

use suda\framework\Config;
use suda\database\DataSource;
use suda\application\Application;
use suda\database\exception\SQLException;
use suda\database\connection\observer\Observer;
use suda\application\Resource as ApplicationResource;

/**
 * Class Database
 * @package suda\application\database
 */
class Database
{
    /**
     * 应用引用
     *
     * @var Application
     */
    protected static $application;

    /**
     * 从应用创建表
     *
     * @param Application $application
     * @return void
     */
    public static function loadApplication(Application $application)
    {
        static::$application = $application;
    }

    /**
     * Get 应用引用
     *
     * @return  Application
     */
    public static function application()
    {
        return static::$application;
    }

    /**
     * 获取默认的数据源
     * @return DataSource
     * @throws SQLException
     */
    public static function getDefaultDataSource():DataSource
    {
        return static::getDataSource('default');
    }
    
    /**
     * @param string $name
     * @return DataSource
     * @throws SQLException
     */
    public static function getDataSource(string $name)
    {
        return static::getDataSourceFrom(static::$application->getResource(), $name);
    }

    /**
     * @param ApplicationResource $resource
     * @param string $groupName
     * @return DataSource
     * @throws SQLException
     */
    public static function getDataSourceFrom(ApplicationResource $resource, string $groupName)
    {
        $group = $groupName === 'default' ? '': '-'. $groupName;
        $dataSourceConfigPath = $resource->getConfigResourcePath('config/data-source'.$group);
        $dataSource = new DataSource;
        if ($dataSourceConfigPath !== null) {
            $observer = new DebugObserver(static::$application->debug());
            $dataSourceConfig = Config::loadConfig($dataSourceConfigPath);
            foreach ($dataSourceConfig as $name => $config) {
                $enable = $config['enable'];
                if ($enable) {
                    static::applyDataSource(
                        $dataSource,
                        $observer,
                        $name,
                        $config['type'] ?? 'mysql',
                        $config['mode'] ?? '',
                        $config
                    );
                }
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
    protected static function applyDataSource(
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
}
