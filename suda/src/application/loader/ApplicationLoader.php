<?php
namespace suda\application\loader;

use suda\framework\Context;
use suda\application\Application;
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
     * @var Application
     */
    protected $application;

    /**
     * 运行环境
     *
     * @var Context
     */
    protected $context;

    public function __construct(Application $application, Context $context)
    {
        $this->context = $context;
        $this->application = $application;
        $this->application->setContext($context);
    }

    public function load()
    {
        $this->registerModule();
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
