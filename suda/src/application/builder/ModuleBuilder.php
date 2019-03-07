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
     * @return Module
     */
    public static function build(string $path):Module
    {
        $config = Config::loadConfig($path, ['path' => $path]);
        $name = dirname($path);
        $version = '1.0.0';
        if ($config) {
            if (array_key_exists('name', $config)) {
                $name = $config['name'];
            }
            if (array_key_exists('version', $config)) {
                $version = $config['version'];
            }
        }
        $module = new Module($name, $version, $config);
        return $module;
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
        if (
            $extension !== 'mod' &&
            $extension !== 'module') {
            return null;
        }
        $zip = new ZipArchive;
        if ($zip->open($path, ZipArchive::CHECKCONS)) {
            $unzipPath = $unpackPath.'/'. pathinfo($path, PATHINFO_FILENAME) .'-'.substr(md5_file($path), 0, 8);
            $zip->extractTo($unzipPath);
            $zip->close();
            return Config::resolve($unzipPath.'/module');
        }
        return null;
    }

    /**
     * 扫描模块
     *
     * @param string $scanPaths
     * @param string $extractPath
     * @return Iterator
     */
    public static function scan(string $modulesPath, string $extractPath): Iterator
    {
        $enabledPack = \class_exists('ZipArchive');
         
        foreach (FileSystem::read($modulesPath) as $path) {
            if (is_file($path) && $enabledPack) {
                if ($configPath = static::checkPack($path, $extractPath)) {
                    yield static::build($configPath);
                }
            } elseif (is_dir($path)) {
                if ($configPath = static::check($path)) {
                    yield static::build($configPath);
                }
            }
        }
    }
}
