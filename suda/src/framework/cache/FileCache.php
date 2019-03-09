<?php
namespace suda\framework\cache;

use suda\framework\Cache;
use suda\framework\filesystem\FileSystem;

/**
 * 文件缓存
 */
class FileCache implements Cache
{
    /**
     * 缓存路径
     *
     * @var string
     */
    protected $path;

    /**
     * 默认过期时间
     *
     * @var int
     */
    protected $expire;

    public function __construct(array $config = [])
    {
        $this->path = $this->getSavePath($config);
        $this->expire = $config['expire'] ?? 86400;
        FileSystem::makes($this->path);
    }

    /**
     * 设置 Cache
     *
     * @param string $name
     * @param mixed $value
     * @return bool
     */
    public function set(string $name, $value, int $expire = null):bool
    {
        $path = $this->getFilePath($name);
        $value = serialize($value);
        if (null === $expire) {
            $expire = time() + $this->expire;
        }
        return FileSystem::put($path, $expire.'|'.$value);
    }

    /**
     * 获取Cache
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get(string $name, $default = null)
    {
        if ($this->has($name)) {
            $path = $this->getFilePath($name);
            $value = FileSystem::get($path);
            list($time, $value) = explode('|', $value, 2);
            if (time() < intval($time)) {
                return unserialize($value);
            } else {
                $this->delete($path);
            }
        }
        return $default;
    }

    /**
     * 删除一个或者全部Cache数据
     *
     * @param string|null $name
     * @return bool
     */
    public function delete(?string $name = null):bool
    {
        if ($name === null) {
            return $this->clear();
        }
        return FileSystem::delete($this->getFilePath($name));
    }

    /**
     * 检测是否存在Cache
     *
     * @param string $name
     * @return boolean
     */
    public function has(string $name):bool
    {
        return FileSystem::exist($this->getFilePath($name));
    }

    /**
     * 清除 Cache
     *
     * @return boolean
     */
    public function clear():bool
    {
        return FileSystem::delete($this->path);
    }

    /**
     * 获取缓存路径
     *
     * @param array $config
     * @return string
     */
    protected function getSavePath(array $config):string
    {
        if (array_key_exists('path', $config)) {
            return $config['path'];
        } elseif (\defined('SUDA_DATA')) {
            return constant('SUDA_DATA').'/cache';
        } else {
            throw new \Exception('file cache save path missing');
        }
    }
    
    protected function getFilePath(string $name)
    {
        return $this->path.'/'.md5($name).'.cache';
    }
}
