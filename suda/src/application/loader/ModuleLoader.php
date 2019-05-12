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
     * @param Application $application
     * @param Module $module
     */
    public function __construct(Application $application, Module $module)
    {
        $this->module = $module;
        $this->application = $application;
    }

    public function toLoaded()
    {
        $this->loadVendorIfExist();
        $this->loadEventListener();
        $this->loadShareLibrary();
        $this->loadExtraModuleResourceLibrary();
        $this->application->debug()->info('loaded - '.$this->module->getFullName());
    }

    /**
     * @throws Exception
     */
    public function toReachable()
    {
        $this->loadRoute();
        $this->application->debug()->info('reachable = '.$this->module->getFullName());
    }

    public function toRunning()
    {
        $this->checkFrameworkVersion();
        $this->checkRequirements();
        $this->loadPrivateLibrary();
        $this->application->setRunning($this->module);
        $this->application->debug()->info('run + '.$this->module->getFullName());
    }

    /**
     * 检查框架依赖
     *
     * @return void
     */
    protected function checkRequirements()
    {
        if ($require = $this->module->getProperty('require')) {
            foreach ($require as $module => $version) {
                $this->assertModuleVersion($module, $version);
            }
        }
    }

    protected function assertModuleVersion(string $module, string $version)
    {
        $target = $this->application->find($module);
        if ($target === null) {
            throw new ApplicationException(
                sprintf('%s module need %s %s but not exist', $this->module->getFullName(), $module, $version),
                ApplicationException::ERR_MODULE_REQUIREMENTS
            );
        }
        if (static::versionCompare($version, $target->getVersion()) !== true) {
            throw new ApplicationException(
                sprintf(
                    '%s module need %s version %s',
                    $this->module->getFullName(),
                    $target->getName(),
                    $target->getVersion()
                ),
                ApplicationException::ERR_MODULE_REQUIREMENTS
            );
        }
    }

    /**
     * 检查模块需求
     *
     * @return void
     */
    protected function checkFrameworkVersion()
    {
        if ($sudaVersion = $this->module->getProperty('suda')) {
            if (static::versionCompare($sudaVersion, SUDA_VERSION) !== true) {
                throw new ApplicationException(
                    sprintf('%s module need suda version %s', $this->module->getFullName(), $sudaVersion),
                    ApplicationException::ERR_FRAMEWORK_VERSION
                );
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

    protected function loadExtraModuleResourceLibrary()
    {
        $import = $this->module->getConfig('module-resource', []);
        if (count($import)) {
            foreach ($import as $name => $path) {
                if ($module = $this->application->find($name)) {
                    $module->getResource()->addResourcePath(
                        Resource::getPathByRelativePath($path, $this->module->getPath())
                    );
                }
            }
        }
    }

    protected function loadEventListener()
    {
        if ($path = $this->module->getResource()->getConfigResourcePath('config/event')) {
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
     * @throws Exception
     */
    protected function loadRoute()
    {
        foreach ($this->application->getRouteGroup() as $group) {
            $this->loadRouteGroup($group);
        }
    }

    /**
     * 导入 ClassLoader 配置
     *
     * @param array $import
     * @param string $relativePath
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
     * @throws Exception
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
     * @throws Exception
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

    /**
     * 比较版本
     *
     * @param string $version 比较用的版本，包含比较符号
     * @param string $compire 对比的版本
     * @return bool
     */
    protected static function versionCompare(string $version, string $compire)
    {
        if (preg_match('/^(<=?|>=?|<>|!=)(.+)$/i', $version, $match)) {
            list($s, $op, $ver) = $match;
            return  version_compare($compire, $ver, $op);
        }
        return version_compare($compire, $version, '>=');
    }
}
