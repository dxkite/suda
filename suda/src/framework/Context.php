<?php
namespace suda\framework;

use suda\framework\Cache;
use suda\framework\Event;
use suda\framework\Route;
use suda\framework\Config;
use suda\framework\http\Request;
use suda\framework\loader\Loader;
use suda\framework\cache\FileCache;
use suda\framework\context\PHPContext;

/**
 * 环境容器
 */
class Context extends PHPContext
{
    /**
     * 事件监听器
     *
     * @var Event
     */
    protected $event;

    /**
     * 路由匹配工具
     *
     * @var Route
     */
    protected $route;

    /**
     * 缓存
     *
     * @var Cache
     */
    protected $cache;
    
    public function __construct(Request $request, Config $config, Loader $loader)
    {
        parent::__construct($request, $config, $loader);
        $this->event = new Event;
        $this->route = new Route;
    }

    /**
     * 获取路由
     *
     * @return \suda\framework\Route
     */
    public function route():Route
    {
        return $this->route;
    }

    /**
     * 获取缓存
     *
     * @return \suda\framework\Cache
     */
    public function cache(): Cache
    {
        if ($this->cache === null) {
            $this->setCache($this->getDefaultCache());
        }
        return $this->cache;
    }

    /**
     * 获取事件
     *
     * @return \suda\framework\Event
     */
    public function event():Event
    {
        return $this->event;
    }

    /**
     * 创建Cache
     *
     * @param string $cacheName
     * @param array $cacheConfig
     * @return \suda\framework\Cache
     */
    protected function createCacheFrom(string $cacheName, array $cacheConfig):Cache
    {
        return new $cacheName($cacheConfig);
    }

    /**
     * 获取默认缓存
     *
     * @return \suda\framework\Cache
     */
    protected function getDefaultCache():Cache
    {
        return $this->createCacheFrom($this->conf('cache.class', FileCache::class), $this->conf('cache', []));
    }

    /**
     * Get 事件监听器
     *
     * @return  Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set 事件监听器
     *
     * @param  Event  $event  事件监听器
     *
     * @return  self
     */
    public function setEvent(Event $event)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get 缓存
     *
     * @return  Cache
     */
    public function getCache()
    {
        return $this->cache();
    }

    /**
     * Set 缓存
     *
     * @param  Cache  $cache  缓存
     *
     * @return  self
     */
    public function setCache(Cache $cache)
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * 加载事件
     *
     * @param string $path
     * @return void
     */
    public function loadEvent(string $path)
    {
        $listener = Config::loadConfig($path, $this->config);
        if (\is_array($listener)) {
            $this->event->load($listener);
        }
    }
}
