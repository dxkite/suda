<?php
namespace suda\application\loader;

use Exception;
use suda\framework\Config;
use suda\application\Module;
use suda\application\Resource;
use suda\application\Application;
use suda\framework\filesystem\FileSystem;
use suda\application\builder\ApplicationBuilder;
use suda\application\exception\ApplicationException;

/**
 * 应用程序
 */
class ModuleLoader extends ModuleLoaderUtil
{
    public function toLoad()
    {
        $this->loadVendorIfExist();
        $this->loadShareLibrary();
        $this->application->debug()->info('loaded - '.$this->module->getFullName());
    }

    public function toActive()
    {
        $this->loadConfig();
        $this->application->debug()->info('active = '.$this->module->getFullName());
    }

    /**
     * @throws Exception
     */
    public function toReachable()
    {
        $this->loadRoute();
        $this->application->debug()->info('reachable # '.$this->module->getFullName());
    }

    public function toRunning()
    {
        $this->checkRequirements();
        $this->loadPrivateLibrary();
        $this->application->setRunning($this->module);
        $this->application->debug()->info('run + '.$this->module->getFullName());
    }

    /**
     * 加载模块额外资源
     */
    public function loadExtraModuleResourceLibrary()
    {
        $resource = $this->module->getProperty('module-resource', []);
        if (count($resource)) {
            foreach ($resource as $name => $path) {
                if ($find = $this->application->find($name)) {
                    $find->getResource()->addResourcePath(
                        Resource::getPathByRelativePath($path, $this->module->getPath())
                    );
                }
            }
        }
    }

    protected function loadShareLibrary()
    {
        $import = $this->module->getProperty('import.share', []);
        if (count($import)) {
            $this->importClassLoader($import, $this->module->getPath());
        }
    }

    protected function loadPrivateLibrary()
    {
        $import = $this->module->getProperty('import.src', []);
        if (count($import)) {
            $this->importClassLoader($import, $this->module->getPath());
        }
    }

    public function loadVendorIfExist()
    {
        $vendorAutoload = $this->module->getPath().'/vendor/autoload.php';
        if (FileSystem::exist($vendorAutoload)) {
            require_once $vendorAutoload;
        }
    }

    protected function loadConfig()
    {
        $this->loadModuleConfig($this->module);
    }

    protected function loadModuleConfig(Module $module)
    {
        $this->loadBaseConfig($module);
        $this->loadEventListener($module);
    }

    protected function loadBaseConfig(Module $module)
    {
        $path = $module->getResource()->getConfigResourcePath('config/config');
        if ($path !== null && ($config = Config::loadConfig($path, $module->getProperty())) !== null) {
            $module->setConfig($config);
        }
    }

    protected function loadEventListener(Module $module)
    {
        if ($path = $module->getResource()->getConfigResourcePath('config/event')) {
            $event = Config::loadConfig($path, [
                'module' => $this->module->getName(),
                'property' => $this->module->getProperty(),
                'config' => $this->module->getConfig(),
            ]);
            if (is_array($event)) {
                $this->application->event()->load($event);
            }
        }
    }

    /**
     * 加载路由
     */
    protected function loadRoute()
    {
        foreach ($this->application->getRouteGroup() as $group) {
            $this->loadRouteGroup($group);
        }
    }

    /**
     * 加载路由组
     *
     * @param string $groupName
     * @return void
     */
    protected function loadRouteGroup(string $groupName)
    {
        $group = $groupName === 'default' ? '': '-'. $groupName;
        if ($path = $this->module->getResource()->getConfigResourcePath('config/route'.$group)) {
            $routeConfig = Config::loadConfig($path, [
                'module' => $this->module->getName(),
                'group' => $groupName,
                'property' => $this->module->getProperty(),
                'config' => $this->module->getConfig(),
            ]);
            if ($routeConfig !== null) {
                $prefix = $this->module->getConfig('route-prefix.'.$groupName, '');
                $this->loadRouteConfig($prefix, $groupName, $routeConfig);
            }
        }
    }

    /**
     * 加载模块路由配置
     *
     * @param string $prefix
     * @param string $groupName
     * @param array $routeConfig
     * @return void
     */
    protected function loadRouteConfig(string $prefix, string $groupName, array $routeConfig)
    {
        $module =  $this->module->getFullName();
        foreach ($routeConfig as $name => $config) {
            $exname = $this->application->getRouteName($name, $module, $groupName);
            $method = $config['method'] ?? [];
            $attributes = [];
            $attributes['module'] = $module;
            $attributes['config'] = $config;
            $attributes['group'] = $groupName;
            $attributes['route'] = $exname;
            $uri = $config['uri'] ?? '/';
            $anti = array_key_exists('anti-prefix', $config) && $config['anti-prefix'];
            if ($anti) {
                $uri = '/'.trim($uri, '/');
            } else {
                $uri = '/'.trim($prefix . $uri, '/');
            }
            $this->application->request($method, $exname, $uri, $attributes);
        }
    }
}
