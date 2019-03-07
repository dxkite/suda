<?php
namespace suda\application\builder;

use suda\framework\Config;
use suda\framework\Context;
use suda\application\Resource;
use suda\application\Application;
use suda\framework\loader\Loader;
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
     * @param Context $context
     * @param string $path
     * @return Application
     */
    public static function build(Context $context, string $path):Application
    {
        $manifast = static::resolveManifastPath($path);
        $manifastConfig = Config::loadConfig($manifast);
        if (\array_key_exists('import', $manifastConfig)) {
            static::importClassLoader($context->get('loader'), $manifastConfig['import'], $path);
        }
        $applicationClass = $manifastConfig['application'] ?? Application::class;
        $application = new $applicationClass($path, $manifastConfig);
        $application->setContext($context);
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

    public static function importClassLoader(Loader $loader, array $import, string $relativePath)
    {
        foreach ($import as $name => $path) {
            $path = Resource::getPathByRelativedPath($path, $relativePath);
            if (\is_numeric($name)) {
                $loader->addIncludePath($path);
            } elseif (is_file($path)) {
                $loader->import($path);
            } else {
                $loader->addIncludePath($path, $name);
            }
        }
    }
}
