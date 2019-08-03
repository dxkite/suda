<?php

namespace suda\framework;

use function is_array;
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

    /**
     * PHP错误调试
     *
     * @var Debugger
     */
    protected $debug;

    /**
     * 创建PHP环境
     *
     * @param Config $config
     * @param Loader $loader
     * @param Event|null $event
     * @param Route|null $route
     */
    public function __construct(Config $config, Loader $loader, ?Event $event = null, ?Route $route = null)
    {
        parent::__construct($config, $loader);
        $this->event = $event ?: new Event;
        $this->route = $route ?: new Route;
    }

    /**
     * 获取路由
     *
     * @return Route
     */
    public function route(): Route
    {
        return $this->route;
    }

    /**
     * 获取缓存
     *
     * @return Cache
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
     * @return Event
     */
    public function event(): Event
    {
        return $this->event;
    }

    /**
     * 创建Cache
     *
     * @param string $cacheName
     * @param array $cacheConfig
     * @return Cache
     */
    protected function createCacheFrom(string $cacheName, array $cacheConfig): Cache
    {
        return new $cacheName($cacheConfig);
    }

    /**
     * 获取默认缓存
     *
     * @return Cache
     */
    protected function getDefaultCache(): Cache
    {
        $config = $this->conf('cache', []);
        $cacheClass = $this->conf('cache.class', FileCache::class);
        $realClassName = Loader::realName($cacheClass);
        return $this->createCacheFrom($realClassName, $config);
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
     * @param Event $event 事件监听器
     *
     * @return $this
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
     * @param Cache $cache 缓存
     *
     * @return $this
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
        if (is_array($listener)) {
            $this->event->load($listener);
        }
    }

    /**
     * Get 路由匹配工具
     *
     * @return  Route
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Set 路由匹配工具
     *
     * @param Route $route 路由匹配工具
     *
     * @return $this
     */
    public function setRoute(Route $route)
    {
        $this->route = $route;
        return $this;
    }

    /**
     * Get pHP错误调试
     *
     * @return  Debugger
     */
    public function getDebug()
    {
        return $this->debug;
    }

    /**
     * Set PHP错误调试
     *
     * @param Debugger $debug PHP错误调试
     *
     * @return $this
     */
    public function setDebug(Debugger $debug)
    {
        $this->debug = $debug;
        return $this;
    }

    /**
     * 获取调试工具
     *
     * @return Debugger
     */
    public function debug(): Debugger
    {
        return $this->debug;
    }
}
