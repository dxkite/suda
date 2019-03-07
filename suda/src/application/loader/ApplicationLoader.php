<?php
namespace suda\application;

use suda\framework\Config;
use suda\framework\config\PathResolver;

/**
 * 应用程序
 */
class ApplicationLoader
{
    /**
     * 从路径加载应用
     *
     * @param string $path
     * @return Application
     */
    public static function load(string $path):Application
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

    public static function loadModules(Application $application) {
        foreach ($application->getModulePaths() as  $path) {
            static::registerModuleFrom($application, $path);
        }
    }


    public static function registerModuleFrom(Application $application, string $path) {
        
    }
}
