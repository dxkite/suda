<?php
namespace suda\framework\route;

use Iterator;
use ArrayIterator;
use IteratorAggregate;
use suda\framework\route\RouteMatcher;

/**
 * 路由集合
 *
 */
class RouteCollection implements IteratorAggregate
{
    /**
     * 属性
     *
     * @var RouteMatcher[]
     */
    protected $collection = [];

    /**
     * 创建集合
     *
     * @param RouteMatcher[] $collection
     */
    public function __construct(array $collection = [])
    {
        $this->mergeArray($collection);
    }

    /**
     * 合并集合
     *
     * @param array $collection
     * @return void
     */
    public function mergeArray(array $collection = [])
    {
        $this->collection = array_merge($this->collection, $collection);
    }

    /**
     * 合并
     *
     * @param array $route
     * @return void
     */
    public function merge(RouteCollection $route)
    {
        $this->collection = array_merge($this->collection, $route->collection);
    }

    /**
     * 添加集合
     *
     * @param string $name
     * @param RouteMatcher $collection
     * @return void
     */
    public function add(string $name, RouteMatcher $collection)
    {
        $this->collection[$name] = $collection;
    }

    /**
     * 获取集合
     *
     * @param string $name
     * @return RouteMatcher|null
     */
    public function get(string $name):?RouteMatcher
    {
        return $this->collection[$name] ?? null;
    }

    /**
     * 获取迭代器
     *
     * @return RouteMatcher[]
     */
    public function getCollection():array
    {
        return $this->collection;
    }

    /**
     * 从文件创建
     *
     * @param string $path
     * @return self
     */
    public static function fromFile(string $path)
    {
        $collection = \unserialize(\file_get_contents($path));
        return new static($collection);
    }

    /**
     * 保存到文件
     *
     * @param string $path
     * @return boolean
     */
    public function save(string $path):bool
    {
        return \file_put_contents($path, \serialize($this->collection)) > 0;
    }

    public function getIterator():Iterator
    {
        return new ArrayIterator($this->collection);
    }
}
