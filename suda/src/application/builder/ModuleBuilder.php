<?php
namespace suda\application\builder;

use Iterator;
use ZipArchive;
use suda\framework\Config;
use suda\application\Module;
use suda\framework\config\PathResolver;
use suda\framework\filesystem\FileSystem;

/**
 * 模块构建工具
 */
class ModuleBuilder
{
    /**
     * 从配置文件构建模块
     *
     * @param string $path
     * @param string $propertyPath
     * @return Module
     */
    public static function build(string $path, string $propertyPath):Module
    {
        list($name, $version, $resource, $property) = static::getModuleProperty($path, $propertyPath);
        $module = new Module($name, $version, $path, $property);
        $module->getResource()->registerResourcePath($path, $resource);
        $module->setUnique($module->getConfig('unique', ''));
        return $module;
    }

    /**
     * 获取模块属性
     *
     * @param string $path
     * @param string $propertyPath
     * @return array
     */
    protected static function getModuleProperty(string $path, string $propertyPath)
    {
        $property = Config::loadConfig($propertyPath, ['path' => $path]) ?? [];
        $name = basename($path);
        $version = '1.0.0';
        $resource = './resource';
        if ($property) {
            if (array_key_exists('name', $property)) {
                $name = $property['name'];
            }
            if (array_key_exists('version', $property)) {
                $version = $property['version'];
            }
            if (array_key_exists('resource', $property)) {
                $resource = $property['resource'];
            }
        }
        return [$name, $version, $resource, $property];
    }

    /**
     * 检查模块配置
     *
     * @param string $path
     * @return string|null
     */
    public static function check(string $path): ?string
    {
        return PathResolver::resolve($path.'/module');
    }

    /**
     * 检查ZIP包
     *
     * @param string $path
     * @param string $unpackPath
     * @return string|null
     */
    public static function checkPack(string $path, string $unpackPath): ?string
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        if ($extension !== 'mod' && $extension !== 'module') {
            return null;
        }
        $zip = new ZipArchive;
        if ($zip->open($path, ZipArchive::CHECKCONS)) {
            $unzipPath = $unpackPath.'/'. pathinfo($path, PATHINFO_FILENAME) .'-'.substr(md5_file($path), 0, 8);
            $zip->extractTo($unzipPath);
            $zip->close();
            return PathResolver::resolve($unzipPath.'/module');
        }
        return null;
    }

    /**
     * 扫描模块
     *
     * @param string $modulesPath
     * @param string $extractPath
     * @return Iterator
     */
    public static function scan(string $modulesPath, string $extractPath): Iterator
    {
        $enabledPack = class_exists('ZipArchive');
        foreach (FileSystem::read($modulesPath) as $path) {
            if (is_file($path) && $enabledPack) {
                if ($configPath = static::checkPack($path, $extractPath)) {
                    yield static::build($path, $configPath);
                }
            } elseif (is_dir($path)) {
                if ($configPath = static::check($path)) {
                    yield static::build($path, $configPath);
                }
            }
        }
    }
}
