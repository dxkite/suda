<?php

namespace suda\application;

use suda\application\Resource as ApplicationResource;
use suda\framework\arrayobject\ArrayDotAccess;

/**
 * 模块名
 */
class Module
{
    const LOADED = 1;
    const ACTIVE = 2;
    const REACHABLE = 3;
    const RUNNING = 4;

    /**
     * 全局唯一名称
     *
     * @var string
     */
    protected $unique;

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
     * @var ApplicationResource
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
     * @var array
     */
    protected $property;

    /**
     * 路径
     *
     * @var string
     */
    protected $path;

    /**
     * 创建模块
     * @param string $name
     * @param string $version
     * @param string $path
     * @param array $property
     * @param array $config
     */
    public function __construct(string $name, string $version, string $path, array $property, array $config = [])
    {
        $this->name = $name;
        $this->version = $version;
        $this->path = $path;
        $this->config = $config;
        $this->property = $property;
        $this->resource = new ApplicationResource;
        $this->status = Module::REACHABLE;
    }

    /**
     * Get 版本
     *
     * @return  string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Get 模块名
     *
     * @return  string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * 获取全名
     *
     * @return string
     */
    public function getFullName(): string
    {
        return $this->getName() . ':' . $this->getVersion();
    }

    /**
     * 获取链接安全名
     *
     * @return string
     */
    public function getUriSafeName(): string
    {
        return $this->getName() . '/' . $this->getVersion();
    }

    /**
     * Get 资源路径
     *
     * @return ApplicationResource
     */
    public function getResource(): ApplicationResource
    {
        return $this->resource;
    }

    /**
     * Set 资源路径
     *
     * @param ApplicationResource $resource 资源路径
     *
     * @return  $this
     */
    public function setResource(ApplicationResource $resource)
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
     * @param string|null $name
     * @param mixed $default
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
     * @param string|null $name
     * @param mixed $default
     * @return mixed
     */
    public function getProperty(string $name = null, $default = null)
    {
        if ($name !== null) {
            return ArrayDotAccess::get($this->property, $name, $default);
        }
        return $this->property;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param string $version
     */
    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    /**
     * @param array $property
     */
    public function setProperty(array $property): void
    {
        $this->property = $property;
    }

    /**
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getUnique(): string
    {
        return strlen($this->unique) > 0 ? $this->unique : $this->getFullName();
    }

    /**
     * @param string $unique
     */
    public function setUnique(string $unique): void
    {
        $this->unique = $unique;
    }
}
