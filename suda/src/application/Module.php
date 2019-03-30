<?php
namespace suda\application;

use suda\framework\Config;
use suda\application\Resource;
use suda\framework\arrayobject\ArrayDotAccess;

/**
 * 模块名
 */
class Module
{
    const LOADED = 1;
    const REACHABLE = 2;
    const RUNNING = 3;

    /**
     * 模块名
     *
     * @var string
     */
    protected $name;

    /**
     * 版本
     *
     * @var string
     */
    protected $version;

    /**
     * 资源路径
     *
     * @var Resource
     */
    protected $resource;

    /**
     * 状态
     *
     * @var int
     */
    protected $status;

    /**
     * 模块配置
     *
     * @var array
     */
    protected $config;


    /**
     * 路径
     *
     * @var string
     */
    protected $path;

    /**
     * 创建模块
     *
     * @param string $name
     * @param string $version
     * @param array $config
     */
    public function __construct(string $name, string $version = '1.0.0', string $path, array $config)
    {
        $this->name = $name;
        $this->version = $version;
        $this->path = $path;
        $this->config = $config;
        $this->resource = new Resource;
        $this->status = Module::REACHABLE;
    }

    /**
     * Get 版本
     *
     * @return  string
     */
    public function getVersion():string
    {
        return $this->version;
    }

    /**
     * Get 模块名
     *
     * @return  string
     */
    public function getName():string
    {
        return $this->name;
    }

    /**
     * 获取全名
     *
     * @return string
     */
    public function getFullName():string
    {
        return $this->getName().':'.$this->getVersion();
    }

    /**
     * 获取链接安全名
     *
     * @return string
     */
    public function getUriSafeName():string {
        return $this->getName().'/'.$this->getVersion();
    }

    /**
     * Get 资源路径
     *
     * @return  Resource
     */
    public function getResource():Resource
    {
        return $this->resource;
    }

    /**
     * Set 资源路径
     *
     * @param  Resource  $resource  资源路径
     *
     * @return  self
     */
    public function setResource(Resource $resource)
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * 设置状态
     *
     * @param integer $status
     * @return void
     */
    public function setStatus(int $status)
    {
        $this->status = $status;
    }

    /**
     * Get 模块配置
     *
     * @return  mixed
     */
    public function getConfig(string $name = null, $default = null)
    {
        if ($name !== null) {
            return ArrayDotAccess::get($this->config, $name, $default);
        }
        return $this->config;
    }

    /**
     * Set 模块配置
     *
     * @param  array  $config  模块配置
     *
     * @return  self
     */
    public function setConfig(array $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Get 路径
     *
     * @return  string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Get 状态
     *
     * @return  int
     */
    public function getStatus()
    {
        return $this->status;
    }
}
