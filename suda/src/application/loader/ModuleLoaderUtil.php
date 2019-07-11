<?php


namespace suda\application\loader;

use suda\application\Application;
use suda\application\builder\ApplicationBuilder;
use suda\application\exception\ApplicationException;
use suda\application\Module;

class ModuleLoaderUtil
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

    /**
     * 检查依赖项目
     *
     * @return void
     */
    protected function checkRequirements()
    {
        $this->checkFrameworkVersion();
        if ($require = $this->module->getProperty('require')) {
            foreach ($require as $module => $version) {
                $this->checkModuleRequirements($module, $version);
            }
        }
    }

    /**
     * @param string $module
     * @param string $version
     */
    private function checkModuleRequirements(string $module, string $version)
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
        if ($version = $this->module->getProperty('suda')) {
            if (static::versionCompare($version, SUDA_VERSION) !== true) {
                throw new ApplicationException(
                    sprintf('%s module need suda version %s', $this->module->getFullName(), $version),
                    ApplicationException::ERR_FRAMEWORK_VERSION
                );
            }
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
     * 比较版本
     *
     * @param string $version 比较用的版本，包含比较符号
     * @param string $compare 对比的版本
     * @return bool
     */
    public static function versionCompare(string $version, string $compare)
    {
        if (preg_match('/^(<=?|>=?|<>|!=)(.+)$/i', $version, $match)) {
            list($s, $op, $ver) = $match;
            return  version_compare($compare, $ver, $op);
        }
        return version_compare($compare, $version, '>=');
    }
}
