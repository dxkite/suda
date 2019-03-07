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
    }

    public function toReacheable()
    {
    }

    public function toRunning()
    {
    }

    public function loadShareLibrary()
    {
    }

    public function loadPrivateLibrary()
    {
    }

    public function loadEventListener()
    {
    }
    public function loadRoute()
    {
    }
}
