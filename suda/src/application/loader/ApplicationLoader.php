<?php
namespace suda\application\loader;

use suda\framework\Context;
use suda\application\Application;
use suda\application\loader\ModuleLoader;
use suda\framework\filesystem\FileSystem;
use suda\application\builder\ModuleBuilder;

/**
 * 应用程序
 */
class ApplicationLoader
{
    /**
     * 应用程序
     *
     * @var \suda\application\Application
     */
    protected $application;

    /**
     * 运行环境
     *
     * @var \suda\framework\Context
     */
    protected $context;

    /**
     * 模块加载器
     *
     * @var \suda\application\loader\ModuleLoader[]
     */
    protected $moduleLoader;

    public function __construct(Application $application, Context $context)
    {
        $this->context = $context;
        $this->application = $application;
        $this->application->setContext($context);
    }

    public function load()
    {
        $this->registerModule();
        $this->prepareModuleLoader();
    }

    public function prepareModuleLoader() {
        $modules =  $this->application->getManifast('modules');
        foreach ($modules as $moduleName) {
            if ($module = $this->application->find($moduleName)) {
                $this->moduleLoader[$module->getFullName()] = $module;
            }
        }
    }

    public function registerModule()
    {
        $extractPath = FileSystem::makes(SUDA_DATA .'/extract-module');
        foreach ($this->application->getModulePaths() as  $path) {
            $this->registerModuleFrom($path, $extractPath);
        }
    }

    public function registerModuleFrom(string $path,string $extractPath)
    {
        foreach (ModuleBuilder::scan($path, $extractPath) as $module) {
            $this->application->add($module);
        }
    }
}
