<?php
namespace suda\application\loader;

use suda\framework\Config;
use suda\framework\Request;
use suda\application\Module;
use suda\framework\Response;
use suda\application\Application;
use suda\application\builder\ApplicationBuilder;
use suda\application\processor\RequestProcessor;

/**
 * 应用程序
 */
class ModuleLoader implements RequestProcessor
{
    /**
     * 应用程序
     *
     * @var Application
     */
    protected $application;

    /**
     * 运行环境
     *
     * @var Module
     */
    protected $module;

    /**
     * 模块加载器
     *
     * @param \suda\application\Application $application
     * @param \suda\application\Module $module
     */
    public function __construct(Application $application, Module $module)
    {
        $this->module = $module;
        $this->application = $application;
    }

    public function toLoaded()
    {
        $this->loadEventListener();
        $this->loadShareLibrary();
    }

    public function toReacheable()
    {
        $this->loadRoute();
    }

    public function toRunning()
    {
        $this->loadPrivateLibrary();
    }

    protected function loadShareLibrary()
    {
        $import = $this->module->getConfig('import.share', []);
        if (count($import)) {
            $this->importClassLoader($import, $this->module->getPath());
        }
    }

    protected function loadPrivateLibrary()
    {
        $import = $this->module->getConfig('import.src', []);
        if (count($import)) {
            $this->importClassLoader($import, $this->module->getPath());
        }
    }

    protected function loadEventListener()
    {
        if ($path = $this->module->getResource()->getConfigResourcePath('config/listener')) {
            $listener = Config::loadConfig($path, [
                'module' => $this->module->getName(),
                'config' => $this->module->getConfig(),
            ]);
            $this->application->getContext()->get('event')->load($listener);
        }
    }

    protected function loadRoute()
    {
        foreach ($this->application->getRouteGroup() as  $group) {
            $this->loadRouteGroup($group);
        }
    }

    /**
     * 导入 ClassLoader 配置
     *
     * @param array $import
     * @return void
     */
    protected function importClassLoader(array $import, string $relativePath)
    {
        ApplicationBuilder::importClassLoader($this->application->getContext()->get('loader'), $import, $relativePath);
    }

    protected function loadRouteGroup(string $groupName)
    {
        $group = $groupName === 'default' ? '': '-'. $groupName;
        if ($path = $this->module->getResource()->getConfigResourcePath('config/router'.$group)) {
            $routeConfig = Config::loadConfig($path, [
                'module' => $this->module->getName(),
                'group' => $groupName,
                'config' => $this->module->getConfig(),
            ]);
            if ($routeConfig !== null) {
                $this->loadRouteConfig($routeConfig);
            }
        }
    }

    protected function loadRouteConfig(array $routeConfig)
    {
        foreach ($routeConfig as $name => $config) {
            $exname = $this->module->getFullName().':'.$name;
            $runnable = [ $this, 'onRequest'];
            $method = $config['method'] ?? [];
            $attriute = [];
            $attriute['module'] = $this->module->getFullName();
            $attriute['route'] = $config;
            $this->application->getContext()->get('route')->request($method, $exname, $config['url'] ?? '/', $runnable, $attriute);
        }
    }

    public function onRequest(Request $request, Response $response)
    {
        $this->toRunning();
        $request->setAttribute('context', $this->application->getContext());
        return $this->application->onRequest($request, $response);
    }
}
