<?php
namespace suda\application\template;

use suda\framework\Config;
use suda\framework\Request;
use suda\framework\Response;
use suda\application\Resource;
use suda\application\Application;
use suda\framework\filesystem\FileSystem;
use suda\application\template\ModuleTemplate;

/**
 * 模板
 */
class TemplateUtil  
{
    /**
     * 获取配置
     *
     * @param \suda\application\Application $application
     * @param string|null $module
     * @return mixed
     */
    public static function getConfig(Application $application, ?string $module)
    {
        $configPath = static::getResource($application, $module)->getConfigResourcePath(static::getTemplatePath($application).'/config');
        $config = [];
        if ($configPath !== null) {
            $config = Config::loadConfig($configPath) ?? [];
        }
        $config = static::fixConfig($application, $module, $config);
        return $config;
    }

    /**
     * 修正配置
     *
     * @param \suda\application\Application $application
     * @param string|null $module
     * @return array
     */
    protected static function fixConfig(Application $application, ?string $module, array $config)
    {
        if (!\array_key_exists('assets-prefix', $config)) {
            $config['assets-prefix'] = defined('SUDA_ASSETS') ? \constant('SUDA_ASSETS'): 'assets';
        }
        if (!\array_key_exists('static', $config)) {
            $config['static'] = 'static';
        }
        if (!\array_key_exists('assets-path', $config)) {
            $config['assets-path'] = SUDA_PUBLIC. '/'.$config['assets-prefix'];
        }
        if (!array_key_exists('static-name', $config)) {
            $config['uri-name'] = static::getSafeUriName($application, $module);
        }
        return $config;
    }

    /**
     * 获取安全路径名
     *
     * @param \suda\application\Application $application
     * @param string|null $module
     * @return string
     */
    public static function getSafeUriName(Application $application, ?string $module)
    {
        if ($module !== null) {
            $moduleObj = $application->find($module);
            if ($moduleObj !== null) {
                return $moduleObj->getUriSafeName();
            }
        }
        return 'application';
    }

    /**
     * 获取资源静态前缀
     *
     * @param string|null $module
     * @return string
     */
    public static function getAssetStaticRoot(Application $application, ?string $module):string
    {
        $config = static::getConfig($application, $module);
        return '/'.$config['assets-prefix'].'/'.$config['static'];
    }

    /**
     * 获取资源前缀
     *
     * @param string|null $module
     * @return string
     */
    public static function getAssetRoot(Application $application, ?string $module):string
    {
        $config = static::getConfig($application, $module);
        return '/'.$config['assets-prefix'];
    }
    
    /**
     * 获取请求资源头
     *
     * @param \suda\application\Application $application
     * @param \suda\framework\Request $request
     * @param string|null $module
     * @return string
     */
    public static function getRequestAsset(Application $application, Request $request, ?string $module = null):string
    {
        $assetRoot = static::getAssetRoot($application, $module);
        if (in_array($request->getIndex(), $application->conf('index', ['/index.php']))) {
            $name = static::writableAssets($application, $module) ? dirname($request->getIndex()):$request->getIndex();
            return rtrim(str_replace('\\', '/', $name), '/').$assetRoot;
        }
        return rtrim(str_replace('\\', '/', $request->getIndex()), '/').$assetRoot;
    }

    /**
     * 是否写入资源文件
     *
     * @param \suda\application\Application $application
     * @param string|null $module
     * @return boolean
     */
    public static function writableAssets(Application $application, ?string $module):bool
    {
        if ($application->conf('assets-auto-write', true) === false) {
            return false;
        }
        $config = static::getConfig($application, $module);
        return FileSystem::isWritable($config['assets-path']);
    }

    /**
     * 获取请求资源头
     *
     * @param \suda\application\Application $application
     * @param \suda\framework\Request $request
     * @param string|null $module
     * @return string
     */
    public static function getStaticRequestAsset(Application $application, Request $request, ?string $module = null):string
    {
        $assetRoot = static::getAssetRoot($application, $module);
        $name = static::writableAssets($application, $module) ? dirname($request->getIndex()):$request->getIndex();
        return rtrim(str_replace('\\', '/', $name), '/').$assetRoot;
    }

    /**
     * 获取模板资源
     *
     * @param \suda\application\Application $application
     * @param string|null $module
     * @return Resource
     */
    public static function getResource(Application $application, ?string $module): Resource
    {
        if ($module !== null && ($moduleObj = $application->find($module))) {
            return $moduleObj->getResource();
        }
        return $application->getResource();
    }

    /**
     * 获取模板路径
     *
     * @param \suda\application\Application $application
     * @return string
     */
    public static function getTemplatePath(Application $application)
    {
        return 'template/'.$application->getStyle();
    }
}
