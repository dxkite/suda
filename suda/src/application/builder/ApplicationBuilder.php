<?php
namespace suda\application\builder;

use suda\framework\Config;
use suda\application\Application;
use suda\framework\config\PathResolver;
use suda\framework\filesystem\FileSystem;

/**
 * 应用程序
 */
class ApplicationBuilder
{
    /**
     * 从路径加载应用
     *
     * @param string $path
     * @return Application
     */
    public static function build(string $path):Application
    {
        $manifast = static::resolveManifastPath($path);
        $application = new Application($path, Config::loadConfig($manifast));
        return $application;
    }
    
    /**
     * 获取Manifast路径
     *
     * @param string $path
     * @return string
     */
    public static function resolveManifastPath(string $path):string
    {
        $manifast = PathResolver::resolve($path.'/manifast');
        if ($manifast === null) {
            FileSystem::copyDir(SUDA_RESOURCE.'/app', $path);
            $manifast = PathResolver::resolve($path.'/manifast');
        }
        return $manifast;
    }

    public static function loadModules(Application $application)
    {
        foreach ($application->getModulePaths() as  $path) {
            static::registerModuleFrom($application, $path);
        }
    }


    public static function registerModuleFrom(Application $application, string $path)
    {
    }
}
