<?php
namespace suda\framework;

use suda\framework\config\PathResolver;
use suda\framework\config\ContentLoader;
use suda\framework\arrayobject\ArrayDotAccess;

/**
 * 服务器参数处理
 */
class Config
{

    /**
     * 配置数组
     *
     * @var array
     */
    public $config;

    /**
     * 构建配置
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * 加载配置
     *
     * @param string $path
     * @param array $extra
     * @return self
     */
    public function load(string $path, array $extra = null)
    {
        $data = static::loadConfig($path, $extra ?? $this->config);
        if ($data) {
            $this->assign($data);
        }
        return $this;
    }

    /**
     * 判断配置文件是否存在
     *
     * @param string $path
     * @return boolean
     */
    public function exist(string $path):bool
    {
        return PathResolver::resolve($path) !== null;
    }

    /**
     * 合并配置
     *
     * @param array $config
     * @return array
     */
    public function assign(array $config):array
    {
        return $this->config = array_merge($this->config, $config);
    }

    /**
     * 获取配置
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get(string $name = null, $default = null)
    {
        if (null === $name) {
            return $this->config;
        }
        return ArrayDotAccess::get($this->config, $name, $default);
    }

    /**
     * 设置配置
     *
     * @param string $name
     * @param mixed $value
     * @param mixed $combine 默认参数
     * @return array
     */
    public function set(string $name, $value, $combine = null)
    {
        return ArrayDotAccess::set($this->config, $name, $value, $combine);
    }

    /**
     * 判断配置是否存在
     *
     * @param string $name
     * @return boolean
     */
    public function has(string $name)
    {
        return ArrayDotAccess::exist($this->config, $name);
    }

    /**
     * 从文本载入配置项
     *
     * @param string $path
     * @param array $extra
     * @return array|null
     */
    public static function loadConfig(string $path, array $extra = []):?array
    {
        if (!file_exists($path)) {
            $path = PathResolver::resolve($path);
        }
        if ($path) {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (in_array($ext, ['yaml','yml','json','php','ini'])) {
                return ContentLoader::{'load'.ucfirst($ext)}($path, $extra);
            }
            return ContentLoader::loadJson($path, $extra);
        }
        return null;
    }
}
