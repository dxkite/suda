<?php

namespace suda\application;

use suda\framework\loader\Path;
use suda\framework\config\PathResolver;
use suda\framework\filesystem\FileSystem;
use suda\application\Resource as ApplicationResource;

/**
 * 资源管理器
 */
class Resource
{
    /**
     * 资源路径
     *
     * @var array
     */
    protected $resource;

    /**
     * 资源路径
     *
     * @param array|string $resource
     */
    public function __construct($resource = [])
    {
        $this->resource = is_array($resource) ? $resource : [$resource];
    }

    /**
     * 获取相对的路径
     *
     * @param string $source
     * @param string $relative
     * @return string
     */
    public static function getPathByRelativePath(string $source, string $relative): string
    {
        $path = $source;
        if (Path::isRelativePath($source)) {
            $path = $relative . '/' . $path;
        }
        return Path::toAbsolutePath($path);
    }

    /**
     * 添加资源目录
     *
     * @param string $path
     * @param string|null $prefix
     * @return void
     */
    public function addResourcePath(string $path, string $prefix = null)
    {
        $path = Path::toAbsolutePath($path);
        if (!in_array($path, $this->resource)) {
            if ($prefix !== null) {
                $prefix = trim($prefix, '/');
                $path = [$prefix, $path];
            }
            array_unshift($this->resource, $path);
        }
    }

    /**
     * 注册资源路径
     *
     * @param string $parent
     * @param string $path
     */
    public function registerResourcePath(string $parent, string $path)
    {
        if (strpos($path, ':')) {
            list($prefix, $path) = explode(':', $path, 2);
            $prefix = trim($prefix);
            $path = ApplicationResource::getPathByRelativePath($path, $parent);
            $this->addResourcePath($path, $prefix);
        } else {
            $path = ApplicationResource::getPathByRelativePath($path, $parent);
            $this->addResourcePath($path);
        }
    }


    /**
     * 获取资源文件路径
     *
     * @param string $path
     * @param string $limitPath 父级溢出
     * @return string|null
     */
    public function getResourcePath(string $path, string $limitPath = null): ?string
    {
        foreach ($this->resource as $root) {
            if ($pathInfo = $this->getTarget($root, $path)) {
                list($root, $target) = $pathInfo;
                $templateLimitPath = $limitPath ? $root . '/' . $limitPath : $root;
                if (FileSystem::exist($target)
                    && FileSystem::isOverflowPath($templateLimitPath, $target) === false) {
                    return $target;
                }
            }
        }
        return null;
    }


    /**
     * @param string|array $root
     * @param string $path
     * @return array|null
     */
    private function getTarget($root, string $path)
    {
        if (is_string($root)) {
            return [$root, $root . '/' . $path];
        }
        list($prefix, $root) = $root;
        if (strpos($path, $prefix) === 0) {
            return [$root, $root . '/' . ltrim(substr($path, strlen($prefix)), '/')];
        }
        return null;
    }

    /**
     * 获取配置资源文件路径
     *
     * @param string $path
     * @return string|null
     */
    public function getConfigResourcePath(string $path): ?string
    {
        foreach ($this->resource as $root) {
            if ($pathInfo = $this->getTarget($root, $path)) {
                list($root, $target) = $pathInfo;
                if (is_string($target) && ($target = PathResolver::resolve($target))) {
                    return $target;
                }
            }
        }
        return null;
    }

    /**
     * @return array
     */
    public function getResource(): array
    {
        return $this->resource;
    }
}
