<?php
namespace suda\application\loader;

use suda\framework\Config;
use suda\framework\Request;
use suda\application\Module;
use suda\framework\Response;
use suda\application\Application;
use suda\application\builder\ApplicationBuilder;
use suda\application\processor\RequestProcessor;
use suda\application\exception\ApplicationException;

/**
 * 应用程序
 */
class ModuleLoader
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
        $this->checkFrameworkVersion();
        $this->checkRequirements();
        $this->loadPrivateLibrary();
        $this->application->setRunning($this->module);
    }

    /**
     * 检查框架依赖
     *
     * @return void
     */
    protected function checkRequirements()
    {
        if ($require = $this->module->getConfig('require')) {
            foreach ($require as $module => $version) {
                $this->checkModuleVersion($module, $version);
            }
        }
    }


    protected function checkModuleVersion(string $module, string $version)
    {
        try {
            $target = $this->application->find($module);
            if ($target === null || static::versionCompire($version, $target->getVersion()) !== true) {
                throw new ApplicationException(sprintf('%s module need %s version %s', $this->module->getFullName(), $target->getName(), $target->getVersion()), ApplicationException::ERR_MODULE_REQUIREMENTS);
            }
        } catch (ApplicationException $e) {
            throw new ApplicationException(sprintf('%s module need %s %s but not exist', $this->module->getFullName(), $module, $version), ApplicationException::ERR_MODULE_REQUIREMENTS);
        }
    }

    /**
     * 检查模块需求
     *
     * @return void
     */
    protected function checkFrameworkVersion()
    {
        if ($sudaVersion = $this->module->getConfig('suda')) {
            if (static::versionCompire($sudaVersion, SUDA_VERSION) !== true) {
                throw new ApplicationException(sprintf('%s module need suda version %s', $this->module->getFullName(), $sudaVersion), ApplicationException::ERR_FRAMEWORK_VERSION);
            }
        }
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
            if (\is_array($listener)) {
                $this->application->event()->load($listener);
            }
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
        ApplicationBuilder::importClassLoader($this->application->loader(), $import, $relativePath);
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
            $attriute['route-config'] = $config;
            $attriute['route'] = $exname;
            $this->application->route()->request($method, $exname, $config['url'] ?? '/', $runnable, $attriute);
        }
    }

    public function onRequest(Request $request, Response $response)
    {
        $this->toRunning();
        // 加载语言
        LanguageLoader::load($this->application);
        return $this->application->onRequest($request, $response);
    }

    /**
     * 比较版本
     *
     * @param string $version 比较用的版本，包含比较符号
     * @param string $compire 对比的版本
     * @return bool
     */
    protected static function versionCompire(string $version, string $compire)
    {
        if (preg_match('/^(<=?|>=?|<>|!=)(.+)$/i', $version, $match)) {
            list($s, $op, $ver) = $match;
            return  version_compare($compire, $ver, $op);
        }
        return version_compare($compire, $version, '>=');
    }
}
