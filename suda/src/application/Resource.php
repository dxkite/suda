<?php
namespace suda\application;

use suda\framework\loader\Path;
use suda\framework\config\PathResolver;
use suda\framework\filesystem\FileSystem;
use suda\framework\loader\IncludeManager;

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

    public function __construct(array $resource = [])
    {
        $this->resource = $resource;
    }

    /**
     * 获取相对的路径
     *
     * @param string $source
     * @param string $relative
     * @return string
     */
    public static function getPathByRelativedPath(string $source, string $relative):string
    {
        $path = $source;
        if (Path::isRelativePath($source)) {
            $path = $relative.'/'.$path;
        }
        return Path::toAbsolutePath($path);
    }

    /**
     * 添加资源目录
     *
     * @param string $path
     * @return void
     */
    public function addResourcePath(string $path)
    {
        $path = Path::toAbsolutePath($path);
        if (!\in_array($path, $this->resource)) {
            array_unshift($this->resource, $path);
        }
    }

    /**
     * 获取资源文件路径
     *
     * @param string $path
     * @param string $limitPath 父级溢出
     * @return string|null
     */
    public function getResourcePath(string $path, string $limitPath = null):?string
    {
        foreach ($this->resource as $root) {
            $target = $root.'/'.$path;
            $limitPath = $limitPath ? $root.'/'.$limitPath : $root;
            if (FileSystem::exist($target) && FileSystem::isOverflowPath($limitPath, $target) === false) {
                return $target;
            }
        }
        return null;
    }

    /**
     * 获取配置资源文件路径
     *
     * @param string $path
     * @return string|null
     */
    public function getConfigResourcePath(string $path):?string
    {
        foreach ($this->resource as $root) {
            if ($target = PathResolver::resolve($root.'/'.$path)) {
                return $target;
            }
        }
        return null;
    }
}
