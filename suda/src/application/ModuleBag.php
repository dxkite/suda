<?php
namespace suda\application;

use suda\application\Module;

/**
 * 模块名
 */
class ModuleBag
{
    /**
     * 模块
     *
     * @var Module[]
     */
    protected $module;

    /**
     * 已知全部全名
     *
     * @var array
     */
    protected $knownsFullName = [];

    /**
     * 查找缓存
     *
     * @var array
     */
    protected $cache = [];

    public function add(Module $module)
    {
        $name = $module->getName();
        $version = $module->getVersion();
        $version = $this->formatVersion($version);
        if (!\in_array($name, $this->knownsFullName)) {
            $this->knownsFullName[$name][$version] = $name.':'.$version;
            uksort($this->knownsFullName[$name], [$this, 'sort']);
        }
        $this->module[$name.':'.$version] = $module;
    }

    /**
     * 获取模块
     *
     * @param string $name
     * @return Module|null
     */
    public function get(string $name):?Module
    {
        $full = $this->getFullName($name);
        return  $this->module[$full];
    }

    /**
     * 检测模块是否存在
     *
     * @param string $name
     * @return boolean
     */
    public function exist(string $name) :bool
    {
        return $this->get($name) !== null;
    }

    /**
     * 拆分名称
     * [namespace/]name[:version]:data -> [namespace/name:version,data]
     *
     * @param string $name
     * @param string|null $default
     * @return array
     */
    public function info(string $name, ?string $default = null):array
    {
        $rpos = \strrpos($name, ':');
        if ($rpos > 0) {
            $module = substr($name, 0, $rpos);
            $name = \substr($name, $rpos + 1);
            $moduleFull = $this->getFullName($module);
            return [$moduleFull, $name];
        }
        if ($rpos === 0) {
            return [$default, substr($name, 1)];
        }
        return [$default, $name];
    }

    /**
     * 获取模块全名
     *
     * @param string $name
     * @return string
     */
    public function getFullName(string $name):string
    {
        if (\array_key_exists($name, $this->cache)) {
            return  $this->cache[$name];
        }
        $fullname = $this->createFullName($name);
        return $fullname;
    }

    protected function createFullName(string $name) {
        $version = null;
        $hasVersion = false;
        if (\strpos($name, ':')) {
            $hasVersion = true;
            list($name, $version) = \explode(':', $name, 2);
        }
        $name = $this->getLikeName($name);
        if (\array_key_exists($version, $this->knownsFullName[$name])) {
            return $this->knownsFullName[$name][$version];
        }
        return $hasVersion?$name.':'.$version:end($this->knownsFullName[$name]);
    }
    
    protected function getLikeName(string $name):string
    {
        $names = [];
        foreach (array_keys($this->knownsFullName) as $keyName) {
            if (\strpos($keyName, $name) !== false) {
                $names[] = $keyName;
            }
        }
        if (count($names) === 0) {
            throw new \Exception(\sprintf('module name %s not exist', $name));
        }
        if (count($names) > 1) {
            throw new \Exception(\sprintf('conflict module name %s', $name));
        }
        return $names[0];
    }


    protected function sort(string $a, string $b)
    {
        return \version_compare($a, $b);
    }

    protected function formatVersion(string $version)
    {
        $version = \strtolower($version);
        if (\strpos($version, 'v') === 0) {
            return substr($version, 1);
        }
        return $version;
    }
}
