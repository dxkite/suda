<?php
namespace suda\application\module;

use Iterator;
use ZipArchive;
use suda\framework\Config;
use suda\application\Module;
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
        $config = new Config;
        $config->load($path);
        if (!$config->has('name')) {
            $config->set('name', basename(dirname($path)));
        }
        $module = new Module(dirname($path), $config);
        return $module;
    }
    
    public static function check(string $path): ?string
    {
        return Config::resolve($path.'/module');
    }

    public static function checkPack(string $path, string $unpackPath): ?string
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        if (
            $extension !== 'mod' &&
            $extension !== 'module') {
            return null;
        }
        $zip=new ZipArchive;
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
     * @param array $scanPaths
     * @param string $extractPath
     * @return Iterator
     */
    public static function scan(array $scanPaths, string $extractPath): Iterator
    {
        $enabledPack = \class_exists('ZipArchive');
        foreach ($scanPaths as $modulesPath) {
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
}
