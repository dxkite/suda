<?php
namespace suda\application;

use suda\framework\Request;
use suda\application\AppicationBase;
use suda\application\template\ModuleTemplate;

/**
 * 应用源处理
 */
class ApplicationSource extends AppicationBase
{

    /**
     * 获取URL
     *
     * @param \suda\framework\Request $request
     * @param string $name
     * @param array $parameter
     * @param boolean $allowQuery
     * @param string|null $default
     * @return string|null
     */
    public function getUrl(Request $request, string $name, array $parameter = [], bool $allowQuery = true, ?string $default = null, ?string $group = null):?string
    {
        $url = $this->route->create($this->getRouteName($name, $default, $group), $parameter, $allowQuery);
        return $this->getUrlIndex($request).'/'.ltrim($url, '/');
    }

    /**
     * 获取URL索引
     *
     * @param \suda\framework\Request $request
     * @return string
     */
    protected function getUrlIndex(Request $request):string
    {
        $indexs = $this->conf('indexs') ?? [ 'index.php' ];
        $index = ltrim($request->getIndex(), '/');
        if (!\in_array($index, $indexs)) {
            return $index;
        }
        return '';
    }

    /**
     * 获取模板页面
     *
     * @param string $name
     * @param \suda\framework\Request $request
     * @param string|null $default
     * @return \suda\application\template\ModuleTemplate
     */
    public function getTemplate(string $name, Request $request, ?string $default = null): ModuleTemplate
    {
        if ($default === null && $this->running) {
            $default = $this->running->getFullName();
        }
        return new ModuleTemplate($this->getModuleSourceName($name, $default), $this, $request, $default);
    }

    /**
     * 获取模板下的资源名
     *
     * @param string $name
     * @param string|null $default
     * @return string
     */
    public function getModuleSourceName(string $name, ?string $default = null):string
    {
        if (strpos($name, ':') > 0) {
            list($module, $group, $name) = $this->parseRouteName($name, $default);
        } else {
            $module = $default;
        }
        if ($module !== null && ($moduleObj = $this->find($module))) {
            return $moduleObj->getFullName().':'.$name;
        }
        return $name;
    }

    /**
     * 获取路由全名
     *
     * @param string $name
     * @param string|null $default
     * @return string
     */
    public function getRouteName(string $name, ?string $default = null, ?string $group = null):string
    {
        if (strpos($name, ':') !== false) {
            list($module, $group, $name) = $this->parseRouteName($name, $default, $group);
        } else {
            $module = $default;
        }
        $prefixGroup = $this->getRouteGroupPrefix($group);
        if ($module !== null && ($moduleObj = $this->find($module))) {
            return $moduleObj->getFullName().$prefixGroup.':'.$name;
        }
        return $name;
    }

    /**
     * 拆分路由名
     *
     * @param string $name
     * @param string|null $groupName
     * @return array
     */
    public function parseRouteName(string $name, ?string $default = null, ?string $groupName = null)
    {
        if (strpos($name, ':') !== false) {
            $dotpos = \strrpos($name, ':');
            $module = substr($name, 0, $dotpos);
            $name = substr($name, $dotpos + 1);
            if (strlen($module) === 0) {
                $module = $default;
            }
        } else {
            $module = $default;
        }
        if ($module !== null && strpos($module, '@') !== false) {
            list($module, $groupName) = \explode('@', $module, 2);
            $module = \strlen($module) ? $module : $default;
        }
        return [$module, $groupName, $name];
    }

    /**
     * 获取分组前缀
     *
     * @param string|null $group
     * @return string
     */
    protected function getRouteGroupPrefix(?string $group):string
    {
        return $group === null || $group === 'default' ? '': '@'. $group;
    }
}
