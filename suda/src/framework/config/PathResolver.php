<?php
namespace suda\framework\config;

/**
 * 路径解析器
 * 支持 yaml,yml,json,php,ini 路径做配置
 */
class PathResolver
{
    /**
     * @param string $path
     * @return string|null
     */
    public static function resolve(string $path):?string
    {
        if (file_exists($path) && is_file($path)) {
            return $path;
        }
        $basepath = dirname($path).'/'.pathinfo($path, PATHINFO_FILENAME);

        return static::resolveYaml($basepath) ?? static::resolveExtensions($basepath, ['json','php','ini']);
    }

    /**
     * @param string $basepath
     * @return string|null
     */
    protected static function resolveYaml(string $basepath):?string
    {
        if (file_exists($conf = $basepath.'.yml') || file_exists($conf = $basepath.'.yaml')) {
            if (function_exists('yaml_parse') || class_exists('Spyc') || class_exists('Symfony\Component\Yaml\Yaml')) {
                return $conf;
            }
        }
        return null;
    }

    protected static function resolveExtensions(string $basepath, array $extensions):?string
    {
        foreach ($extensions as $ext) {
            $conf = $basepath.'.'.$ext;
            if (file_exists($conf) && is_file($conf)) {
                return $conf;
            }
        }
        return null;
    }
}
