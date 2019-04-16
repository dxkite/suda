<?php
namespace suda\application;

use suda\orm\DataSource;
use suda\framework\Config;
use suda\framework\Context;
use suda\application\Resource;
use suda\framework\loader\Loader;
use suda\framework\arrayobject\ArrayDotAccess;

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
    protected $manifast;

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
     * @var Resource
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
     * @param array $manifast
     * @param \suda\framework\loader\Loader $loader
     */
    public function __construct(string $path, array $manifast, Loader $loader, string $dataPath = null)
    {
        parent::__construct(new Config(['app' => $manifast]), $loader);
        $this->path = $path;
        $this->routeGroup = $manifast['route-group'] ?? ['default'];
        $this->resource = new Resource([Resource::getPathByRelativedPath($manifast['resource'] ?? './resource', $path)]);
        $this->locate = $manifast['locale'] ?? 'zh-cn';
        $this->style = $manifast['style'] ?? 'default';
        $this->manifast = $manifast;
        $this->dataPath = $dataPath ?? Resource::getPathByRelativedPath($manifast['resource'] ?? './data', $path);
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
     * @return  mixed
     */
    public function getManifast(string $name = null, $default = null)
    {
        if ($name !== null) {
            return ArrayDotAccess::get($this->manifast, $name, $default);
        }
        return $this->manifast;
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
     * @return  Resource
     */
    public function getResource(): Resource
    {
        return $this->resource;
    }

    /**
     * Set 数据源
     *
     * @param  Resource  $resource  数据源
     *
     * @return  self
     */
    public function setResource(Resource $resource)
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
