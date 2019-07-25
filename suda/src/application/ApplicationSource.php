<?php

namespace suda\application;

use function explode;
use function in_array;
use function strlen;
use function strrpos;
use suda\framework\Request;

/**
 * 应用源处理
 */
class ApplicationSource extends ApplicationBase
{

    /**
     * 获取URL
     *
     * @param Request $request
     * @param string $name
     * @param array $parameter
     * @param boolean $allowQuery
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
     * 获取模板下的资源名
     *
     * @param string $name
     * @param string|null $default
     * @return string
     */
    public function getModuleSourceName(string $name, ?string $default = null): string
    {
        if (strpos($name, ':') > 0) {
            list($module, $group, $name) = $this->parseRouteName($name, $default);
        } else {
            $module = $default;
        }
        if ($module !== null && ($moduleObj = $this->find($module))) {
            return $moduleObj->getFullName() . ':' . $name;
        }
        return $name;
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
            list($module, $group, $name) = $this->parseRouteName($name, $default, $group);
        } else {
            $module = $default;
        }
        $prefixGroup = $this->getRouteGroupPrefix($group);
        if ($module !== null && ($moduleObj = $this->find($module))) {
            return $moduleObj->getFullName() . $prefixGroup . ':' . $name;
        }
        return $name;
    }

    /**
     * 拆分路由名
     *
     * @param string $name
     * @param string|null $default
     * @param string|null $groupName
     * @return array
     */
    public function parseRouteName(string $name, ?string $default = null, ?string $groupName = null)
    {
        if (strpos($name, ':') !== false) {
            $dotpos = strrpos($name, ':');
            $module = substr($name, 0, $dotpos);
            $name = substr($name, $dotpos + 1);
            if (strlen($module) === 0) {
                $module = $default;
            }
        } else {
            $module = $default;
        }
        if ($module !== null && strpos($module, '@') !== false) {
            list($module, $groupName) = explode('@', $module, 2);
            $module = strlen($module) ? $module : $default;
        }
        return [$module, $groupName, $name];
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
}
