<?php
namespace suda\application\builder;

use suda\framework\Config;
use suda\application\Resource;
use suda\framework\http\Request;
use suda\application\Application;
use suda\framework\loader\Loader;
use suda\framework\config\PathResolver;
use suda\framework\filesystem\FileSystem;
use suda\application\exception\ApplicationException;

/**
 * 应用程序
 */
class ApplicationBuilder
{
    /**
     * 创建应用
     *
     * @param \suda\framework\http\Request $request
     * @param \suda\framework\loader\Loader $loader
     * @param string $path
     * @return \suda\application\Application
     */
    public static function build(Request $request, Loader $loader, string $path):Application
    {
        $manifast = static::resolveManifastPath($path);
        $manifastConfig = Config::loadConfig($manifast) ?? [];
        if (\array_key_exists('import', $manifastConfig)) {
            static::importClassLoader($loader, $manifastConfig['import'], $path);
        }
        $applicationClass = $manifastConfig['application'] ?? Application::class;
        $application = new $applicationClass($path, $manifastConfig , $request, $loader);
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
        if ($manifast === null) {
            throw new ApplicationException(sprintf('missing manifast in %s', $path), ApplicationException::ERR_MANIFAST_IS_EMPTY);
        } else {
            return $manifast;
        }
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
