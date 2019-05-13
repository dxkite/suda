<?php


namespace suda\application\database;

use suda\application\Application;
use suda\application\exception\ConfigurationException;
use suda\application\Resource as ApplicationResource;
use suda\framework\Config;
use suda\orm\statement\QueryAccess;
use suda\orm\struct\QueryStatement;

class StatementSet
{
    /**
     * 查询操作
     * @var QueryAccess
     */
    protected $access;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $config;

    /**
     * Statement constructor.
     * @param QueryAccess $access
     * @param string $name
     */
    public function __construct(QueryAccess $access, string $name)
    {
        $this->access = $access;
        $this->name = $name;
    }

    /**
     * @param Application $application
     * @return $this
     */
    public function load(Application $application)
    {
        list($resource, $name) = $this->parseNameResource($application, $this->name);
        $this->config = $this->getConfigFrom($resource, $name);
        return $this;
    }

    /**
     * @param string $name
     * @return QueryStatement
     */
    public function build(string $name): QueryStatement
    {
        if (array_key_exists($name, $this->config) === false) {
            throw new ConfigurationException('missing statement @resource:' . $this->name . '#' . $name);
        }
        $config = $this->config[$name];
        return (new QueryStatementBuilder($this->access, $config))->build();
    }

    /**
     * @param ApplicationResource $resource
     * @param string $name
     * @return array
     */
    protected function getConfigFrom(ApplicationResource $resource, string $name): array
    {
        $configPath = $resource->getConfigResourcePath($name);
        if ($configPath !== null) {
            $config = Config::loadConfig($configPath);
            if ($config !== null) {
                return $config;
            }
        }
        throw new ConfigurationException('missing statement @resource:' . $this->name);
    }

    /**
     * @param Application $application
     * @param string $name
     * @return array
     */
    protected function parseNameResource(Application $application, string $name)
    {
        $resource = null;
        if (strpos($name, ':') > 0) {
            list($module, $group, $name) = $application->parseRouteName($name);
            if ($module !== null && ($moduleObj = $application->find($module))) {
                $resource = $moduleObj->getResource();
            }
        }
        if ($resource === null) {
            $resource = $application->getResource();
        }
        return [$resource, $name];
    }
}
