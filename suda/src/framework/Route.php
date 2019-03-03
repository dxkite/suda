<?php
namespace suda\framework;

use suda\framework\Server;
use suda\framework\Request;
use suda\framework\route\MatchResult;
use suda\framework\runnable\Runnable;
use suda\framework\route\RouteMatcher;
use suda\framework\route\uri\UriMatcher;
use suda\framework\route\RouteCollection;

class Route
{
    /**
     * 路由
     *
     * @var RouteCollection
     */
    protected $routes;

    /**
     * 可执行对象
     *
     * @var Runnable[]
     */
    protected $runnable;

    public function __construct()
    {
        $this->routes = new RouteCollection;
        $this->runnable = [];
    }

    /**
     * 创建 GET 路由
     *
     * @param string $name
     * @param string $url
     * @param \suda\framework\runnable\Runnable|\Closure|array|string $runnable
     * @param array $attributes
     * @return void
     */
    public function get(string $name, string $url, $runnable, array $attributes = [])
    {
        $this->request(['GET'], $name, $url, $runnable, $attributes);
    }

    /**
     * 创建 POST 路由
     *
     * @param string $name
     * @param string $url
     * @param \suda\framework\runnable\Runnable|\Closure|array|string $runnable
     * @param array $attributes
     * @return void
     */
    public function post(string $name, string $url, $runnable, array $attributes = [])
    {
        $this->request(['POST'], $name, $url, $runnable, $attributes);
    }

    /**
     * 创建 DELETE 路由
     *
     * @param string $name
     * @param string $url
     * @param \suda\framework\runnable\Runnable|\Closure|array|string $runnable
     * @param array $attributes
     * @return void
     */
    public function delete(string $name, string $url, $runnable, array $attributes = [])
    {
        $this->request(['DELETE'], $name, $url, $runnable, $attributes);
    }

    /**
     * 创建 HEAD 路由
     *
     * @param string $name
     * @param string $url
     * @param \suda\framework\runnable\Runnable|\Closure|array|string $runnable
     * @param array $attributes
     * @return void
     */
    public function head(string $name, string $url, $runnable, array $attributes = [])
    {
        $this->request(['HEAD'], $name, $url, $runnable, $attributes);
    }


    /**
     * 创建 OPTIONS 路由
     *
     * @param string $name
     * @param string $url
     * @param \suda\framework\runnable\Runnable|\Closure|array|string $runnable
     * @param array $attributes
     * @return void
     */
    public function options(string $name, string $url, $runnable, array $attributes = [])
    {
        $this->request(['OPTIONS'], $name, $url, $runnable, $attributes);
    }

    /**
     * 创建 PUT 路由
     *
     * @param string $name
     * @param string $url
     * @param \suda\framework\runnable\Runnable|\Closure|array|string $runnable
     * @param array $attributes
     * @return void
     */
    public function put(string $name, string $url, $runnable, array $attributes = [])
    {
        $this->request(['PUT'], $name, $url, $runnable, $attributes);
    }

    /**
     * 创建 TRACE 路由
     *
     * @param string $name
     * @param string $url
     * @param \suda\framework\runnable\Runnable|\Closure|array|string $runnable
     * @param array $attributes
     * @return void
     */
    public function trace(string $name, string $url, $runnable, array $attributes = [])
    {
        $this->request(['TRACE'], $name, $url, $runnable, $attributes);
    }

    /**
     * 创建路由
     *
     * @param string $name
     * @param string $url
     * @param \suda\framework\runnable\Runnable|\Closure|array|string $runnable
     * @param array $attributes
     * @return void
     */
    public function any(string $name, string $url, $runnable, array $attributes = [])
    {
        $this->request([], $name, $url, $runnable, $attributes);
    }

    /**
     * 添加请求
     *
     * @param array $method
     * @param string $name
     * @param string $url
     * @param \suda\framework\runnable\Runnable|\Closure|array|string $runnable
     * @param array $attributes
     * @return void
     */
    public function request(array $method, string $name, string $url, $runnable, array $attributes = [])
    {
        $matcher = new RouteMatcher($method, $url, $attributes);
        $this->routes->add($name, $matcher);
        $this->runnable[$name] = new Runnable($runnable);
    }

    /**
     * 匹配路由
     *
     * @param Request $request
     * @return MatchResult|null
     */
    public function match(Request $request):? MatchResult
    {
        foreach ($this->routes as $name => $matcher) {
            if (($parameter = $matcher->match($request)) !== null) {
                return new MatchResult($name, $matcher, $this->runnable[$name], $parameter);
            }
        }
        return null;
    }

    /**
     * 创建URL
     *
     * @param string $name
     * @param array $parameter
     * @param boolean $allowQuery
     * @return string|null
     */
    public function create(string $name, array $parameter, bool $allowQuery = true):?string
    {
        if ($matcher = $this->routes->get($name)) {
            return UriMatcher::buildUri($matcher->getMatcher(), $parameter, $allowQuery);
        }
        return null;
    }
}
