<?php
namespace suda\application;

use suda\database\DataSource;
use suda\framework\Config;
use suda\framework\Context;
use suda\framework\loader\Loader;
use suda\framework\arrayobject\ArrayDotAccess;
use suda\application\Resource as ApplicationResource;

/**
 * 应用程序环境
 */
class ApplicationContext extends Context
{
    /**
     * 应用路径
     *
     * @var string
     */
    protected $path;


    /**
     * 数据路径
     *
     * @var string
     */
    protected $dataPath;

    /**
     * 配置数组
     *
     * @var array
     */
    protected $manifest;

    /**
     * 语言
     *
     * @var string
     */
    protected $locate;

    /**
     * 使用的样式
     *
     * @var string
     */
    protected $style;

    /**
     * 路由组
     *
     * @var array
     */
    protected $routeGroup;

    /**
     * 资源目录
     *
     * @var ApplicationResource
     */
    protected $resource;

    /**
     * 数据源
     *
     * @var DataSource
     */
    protected $dataSource;

    /**
     * 创建应用
     *
     * @param string $path
     * @param array $manifest
     * @param Loader $loader
     * @param string|null $dataPath
     */
    public function __construct(string $path, array $manifest, Loader $loader, ?string $dataPath = null)
    {
        parent::__construct(new Config(['app' => $manifest]), $loader);
        $this->setPath($path);
        $this->setManifest($manifest);
        $this->setRouteGroup($manifest['route-group'] ?? ['default']);
        $resource = new Resource();
        $resource->registerResourcePath($path, $manifest['resource'] ?? './resource');
        $this->setResource($resource);
        $this->setLocate($manifest['locale'] ?? 'zh-cn');
        $this->setStyle($manifest['style'] ?? 'default');
        $this->setDataPath($dataPath ?? Resource::getPathByRelativePath($manifest['resource'] ?? './data', $path));
        $this->config->set('data_path', $this->dataPath);
    }

    /**
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * @param array $manifest
     */
    public function setManifest(array $manifest): void
    {
        $this->manifest = $manifest;
    }

    /**
     * @param array $routeGroup
     */
    public function setRouteGroup(array $routeGroup): void
    {
        $this->routeGroup = $routeGroup;
    }

    /**
     * Get 使用的样式
     *
     * @return  string
     */
    public function getStyle()
    {
        return $this->style;
    }

    /**
     * Set 使用的样式
     *
     * @param string $style 使用的样式
     *
     * @return  self
     */
    public function setStyle(string $style)
    {
        $this->style = $style;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocate(): string
    {
        return $this->locate;
    }

    /**
     * @param string $locate
     */
    public function setLocate(string $locate): void
    {
        $this->locate = $locate;
    }


    /**
     * Get 配置数组
     *
     * @param string|null $name
     * @param mixed $default
     * @return  mixed
     */
    public function getManifest(string $name = null, $default = null)
    {
        if ($name !== null) {
            return ArrayDotAccess::get($this->manifest, $name, $default);
        }
        return $this->manifest;
    }

    /**
     * Get 路由组
     *
     * @return  array
     */
    public function getRouteGroup()
    {
        return $this->routeGroup;
    }

    /**
     * Get 数据源
     *
     * @return  ApplicationResource
     */
    public function getResource(): ApplicationResource
    {
        return $this->resource;
    }

    /**
     * Set 数据源
     *
     * @param ApplicationResource $resource 数据源
     *
     * @return  self
     */
    public function setResource(ApplicationResource $resource)
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * Get 数据源
     *
     * @return  DataSource
     */
    public function getDataSource()
    {
        return $this->dataSource;
    }

    /**
     * Set 数据源
     *
     * @param  DataSource  $dataSource  数据源
     *
     * @return  self
     */
    public function setDataSource(DataSource $dataSource)
    {
        $this->dataSource = $dataSource;

        return $this;
    }

    /**
     * Get 应用路径
     *
     * @return  string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Get 数据路径
     *
     * @return  string
     */
    public function getDataPath()
    {
        return $this->dataPath;
    }

    /**
     * Set 数据路径
     *
     * @param string $dataPath 数据路径
     *
     * @return  self
     */
    public function setDataPath(string $dataPath)
    {
        $this->dataPath = $dataPath;

        return $this;
    }
}
