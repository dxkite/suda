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
        $this->path = $path;
        $this->routeGroup = $manifest['route-group'] ?? ['default'];
        $this->resource = new Resource();
        $this->resource->registerResourcePath($path, $manifest['resource'] ?? './resource');
        $this->locate = $manifest['locale'] ?? 'zh-cn';
        $this->style = $manifest['style'] ?? 'default';
        $this->manifest = $manifest;
        $this->dataPath = $dataPath ?? Resource::getPathByRelativePath($manifest['resource'] ?? './data', $path);
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
     * @param  string  $style  使用的样式
     *
     * @return  self
     */
    public function setStyle(string $style)
    {
        $this->style = $style;

        return $this;
    }

    /**
     * Get 语言
     *
     * @return  string
     */
    public function getLocate()
    {
        return $this->locate;
    }

    /**
     * Set 语言
     *
     * @param  string  $locate  语言
     *
     * @return  self
     */
    public function setLocate(string $locate)
    {
        $this->locate = $locate;

        return $this;
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
     * @param  string  $dataPath  数据路径
     *
     * @return  self
     */
    public function setDataPath(string $dataPath)
    {
        $this->dataPath = $dataPath;

        return $this;
    }
}
