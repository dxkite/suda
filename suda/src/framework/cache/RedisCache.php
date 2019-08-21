<?php


namespace suda\framework\cache;


use Redis;
use suda\framework\Cache;

/**
 * Class RedisCache
 * @package suda\framework\cache
 */
class RedisCache implements Cache
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var Redis
     */
    protected $redis;

    /**
     * @var string
     */
    protected $prefix;


    /**
     * RedisCache constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->prefix = $config['prefix'] ?? 'suda_cache:';
        $this->redis = new Redis();
        $this->connect($config);
        $this->redis->setOption(Redis::OPT_PREFIX, $this->prefix);
        $this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
    }

    /**
     * @param array $config
     * @return bool
     */
    private function connect(array $config) {
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? 6379;
        $timeout = floatval($config['timeout'] ?? 0.0);
        $connect = $this->redis->pconnect($host, $port, $timeout);
        if ($connect && array_key_exists('password', $config)) {
            return $this->redis->auth($config['password']);
        }
        return $connect;
    }

    /**
     * 设置 Cache
     *
     * @param string $name
     * @param mixed $value
     * @param int $expire
     * @return bool
     */
    public function set(string $name, $value, int $expire = null): bool
    {
        if (is_integer($expire) && $expire > 0) {
            return $this->redis->set($name, $value, $expire);
        }
        return $this->redis->set($name, $value);
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
            return $this->redis->get($name);
        }
        return $default;
    }

    /**
     * 删除一个或者全部Cache数据
     *
     * @param string|null $name
     * @return bool
     */
    public function delete(?string $name = null): bool
    {
        if (is_null($name)) {
            $this->clear();
        }
        return $this->redis->del($name) > 0;
    }

    /**
     * 检测是否存在Cache
     *
     * @param string $name
     * @return boolean
     */
    public function has(string $name): bool
    {
        return $this->redis->exists($name) > 0;
    }

    /**
     * 清除 Cache
     *
     * @return boolean
     */
    public function clear(): bool
    {
        $keys = $this->redis->keys('*');
        return $this->redis->del($keys) > 0;
    }

    public function __destruct()
    {
        $this->redis->close();
    }
}