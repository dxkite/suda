<?php
namespace suda\application\builder;

use suda\framework\Config;
use suda\application\Resource;
use suda\application\Application;
use suda\framework\loader\Loader;
use suda\framework\config\PathResolver;
use suda\framework\filesystem\FileSystem;
use suda\application\exception\ApplicationException;

/**
 * 应用程序
 * Class ApplicationBuilder
 * @package suda\application\builder
 */
class ApplicationBuilder
{
    /**
     * 创建应用
     * @param Loader $loader
     * @param string $path
     * @param string $dataPath
     * @return Application
     */
    public static function build(Loader $loader, string $path, string $dataPath):Application
    {
        $manifest = static::resolveManifestPath($path);
        $manifestConfig = Config::loadConfig($manifest) ?? [];
        if (array_key_exists('import', $manifestConfig)) {
            static::importClassLoader($loader, $manifestConfig['import'], $path);
        }
        $applicationClass = $manifestConfig['application'] ?? Application::class;
        $application = new $applicationClass($path, $manifestConfig, $loader, $dataPath);
        return $application;
    }
    
    /**
     * 获取Manifest路径
     *
     * @param string $path
     * @return string
     */
    public static function resolveManifestPath(string $path):string
    {
        $manifest = PathResolver::resolve($path.'/manifest');
        if ($manifest === null) {
            FileSystem::copyDir(SUDA_RESOURCE.'/app', $path);
            $manifest = PathResolver::resolve($path.'/manifest');
        }
        if ($manifest === null) {
            throw new ApplicationException(sprintf('missing manifest in %s', $path), ApplicationException::ERR_MANIFAST_IS_EMPTY);
        } else {
            return $manifest;
        }
    }

    public static function importClassLoader(Loader $loader, array $import, string $relativePath)
    {
        foreach ($import as $name => $path) {
            $path = Resource::getPathByRelativePath($path, $relativePath);
            if (is_numeric($name)) {
                $loader->addIncludePath($path);
            } elseif (is_file($path)) {
                $loader->import($path);
            } else {
                $loader->addIncludePath($path, $name);
            }
        }
    }
}
