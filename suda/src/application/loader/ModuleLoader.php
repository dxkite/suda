<?php
namespace suda\application\loader;

use suda\framework\Context;
use suda\application\Module;
use suda\application\Application;

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
        $this->loadPrivateLibrary();
    }

    protected function loadShareLibrary()
    {
    }

    protected function loadPrivateLibrary()
    {
    }

    protected function loadEventListener()
    {
    }

    protected function loadRoute()
    {
    }
}
