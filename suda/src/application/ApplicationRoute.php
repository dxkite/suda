<?php

namespace suda\application;

use suda\framework\loader\Loader;
use function explode;
use function in_array;
use function strlen;
use function strrpos;
use suda\framework\Request;

/**
 * 应用源处理
 */
class ApplicationRoute extends ApplicationModule
{

    /**
     * 路由组
     *
     * @var array
     */
    protected $routeGroup;

    /**
     * ApplicationSource constructor.
     * @param string $path
     * @param array $manifest
     * @param Loader $loader
     * @param string|null $dataPath
     */
    public function __construct(string $path, array $manifest, Loader $loader, ?string $dataPath = null)
    {
        parent::__construct($path, $manifest, $loader, $dataPath);
        $this->setRouteGroup($manifest['route-group'] ?? ['default']);
    }

    /**
     * 获取URL
     *
     * @param Request $request
     * @param string $name
     * @param array $parameter
     * @param bool $allowQuery
     * @param string|null $default
     * @param string|null $group
     * @return string|null
     */
    public function getUrl(
        Request $request,
        string $name,
        array $parameter = [],
        bool $allowQuery = true,
        ?string $default = null,
        ?string $group = null
    ): ?string {
        $group = $group ?? $request->getAttribute('group');
        $default = $default ?? $request->getAttribute('module');
        $url = $this->route->create($this->getRouteName($name, $default, $group), $parameter, $allowQuery);
        return $this->getUrlIndex($request) . ltrim($url, '/');
    }

    /**
     * 获取URL索引
     *
     * @param Request $request
     * @return string
     */
    protected function getUrlIndex(Request $request): string
    {
        $indexArray = $this->conf('index') ?? ['index.php'];
        $rewrite = $this->conf('url_rewrite', false);
        $base = $request->getIndex();
        $index = ltrim($base, '/');
        // 根目录重写开启
        if (in_array($index, $indexArray) && $rewrite) {
            $base = '';
        }
        return $base.'/';
    }

    /**
     * 获取基础URI
     *
     * @param Request $request
     * @param boolean $beautify
     * @return string
     */
    public function getUriBase(Request $request, bool $beautify = true): string
    {
        $index = $beautify ? $this->getUrlIndex($request) : $request->getIndex();
        return $request->getUriBase() . $index;
    }

    /**
     * 获取分组前缀
     *
     * @param string|null $group
     * @return string
     */
    protected function getRouteGroupPrefix(?string $group): string
    {
        return $group === null || $group === 'default' ? '' : '@' . $group;
    }

    /**
     * @param array $routeGroup
     */
    public function setRouteGroup(array $routeGroup): void
    {
        $this->routeGroup = $routeGroup;
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
     * 获取路由全名
     *
     * @param string $name
     * @param string|null $default
     * @param string|null $group
     * @return string
     */
    public function getRouteName(string $name, ?string $default = null, ?string $group = null): string
    {
        if (strpos($name, ':') !== false) {
            list($module, $group, $name) = $this->parseSourceName($name, $default, $group);
        } else {
            $module = $default;
        }
        $prefixGroup = $this->getRouteGroupPrefix($group);
        if ($module !== null && ($moduleObj = $this->find($module))) {
            return $moduleObj->getFullName() . $prefixGroup . ':' . $name;
        }
        return $name;
    }
}
