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

    /**
     * 设置默认
     *
     * @var Runnable
     */
    protected $default;

    public function __construct()
    {
        $this->routes = new RouteCollection;
        $this->runnable = [];
        $this->default = $this->createDefaultRunnable();
    }

    /**
     * 创建 GET 路由
     *
     * @param string $name
     * @param string $url
     * @param \suda\framework\runnable\Runnable|\Closure|array|string $runnable
     * @param array $attributes
     * @return self
     */
    public function get(string $name, string $url, $runnable, array $attributes = [])
    {
        return $this->request(['GET'], $name, $url, $runnable, $attributes);
    }

    /**
     * 创建 POST 路由
     *
     * @param string $name
     * @param string $url
     * @param \suda\framework\runnable\Runnable|\Closure|array|string $runnable
     * @param array $attributes
     * @return self
     */
    public function post(string $name, string $url, $runnable, array $attributes = [])
    {
        return $this->request(['POST'], $name, $url, $runnable, $attributes);
    }

    /**
     * 创建 DELETE 路由
     *
     * @param string $name
     * @param string $url
     * @param \suda\framework\runnable\Runnable|\Closure|array|string $runnable
     * @param array $attributes
     * @return self
     */
    public function delete(string $name, string $url, $runnable, array $attributes = [])
    {
        return $this->request(['DELETE'], $name, $url, $runnable, $attributes);
    }

    /**
     * 创建 HEAD 路由
     *
     * @param string $name
     * @param string $url
     * @param \suda\framework\runnable\Runnable|\Closure|array|string $runnable
     * @param array $attributes
     * @return self
     */
    public function head(string $name, string $url, $runnable, array $attributes = [])
    {
        return $this->request(['HEAD'], $name, $url, $runnable, $attributes);
    }


    /**
     * 创建 OPTIONS 路由
     *
     * @param string $name
     * @param string $url
     * @param \suda\framework\runnable\Runnable|\Closure|array|string $runnable
     * @param array $attributes
     * @return self
     */
    public function options(string $name, string $url, $runnable, array $attributes = [])
    {
        return $this->request(['OPTIONS'], $name, $url, $runnable, $attributes);
    }

    /**
     * 创建 PUT 路由
     *
     * @param string $name
     * @param string $url
     * @param \suda\framework\runnable\Runnable|\Closure|array|string $runnable
     * @param array $attributes
     * @return self
     */
    public function put(string $name, string $url, $runnable, array $attributes = [])
    {
        return $this->request(['PUT'], $name, $url, $runnable, $attributes);
    }

    /**
     * 创建 TRACE 路由
     *
     * @param string $name
     * @param string $url
     * @param \suda\framework\runnable\Runnable|\Closure|array|string $runnable
     * @param array $attributes
     * @return self
     */
    public function trace(string $name, string $url, $runnable, array $attributes = [])
    {
        return $this->request(['TRACE'], $name, $url, $runnable, $attributes);
    }

    /**
     * 创建路由
     *
     * @param string $name
     * @param string $url
     * @param \suda\framework\runnable\Runnable|\Closure|array|string $runnable
     * @param array $attributes
     * @return self
     */
    public function any(string $name, string $url, $runnable, array $attributes = [])
    {
        return $this->request([], $name, $url, $runnable, $attributes);
    }

    /**
     * 添加请求
     *
     * @param array $method
     * @param string $name
     * @param string $url
     * @param \suda\framework\runnable\Runnable|\Closure|array|string $runnable
     * @param array $attributes
     * @return self
     */
    public function request(array $method, string $name, string $url, $runnable, array $attributes = [])
    {
        $matcher = new RouteMatcher($method, $url, $attributes);
        $this->routes->add($name, $matcher);
        $this->runnable[$name] = new Runnable($runnable);
        return $this;
    }

    /**
     * 设置默认运行器
     *
     * @param \suda\framework\runnable\Runnable|\Closure|array|string $runnable
     * @return self
     */
    public function default($runnable)
    {
        $this->default = new Runnable($runnable);
        return $this;
    }

    /**
     * 匹配路由
     *
     * @param Request $request
     * @return Response
     */
    public function match(Request $request, Response $response): Response
    {
        foreach ($this->routes as $name => $matcher) {
            if (($parameter = $matcher->match($request)) !== null) {
                return $this->buildResponse($matcher, $request, $response, $name, $parameter);
            }
        }
        $content =$this->default->run($request, $response);
        if ($content != null) {
            $response->setContent($content);
        }
        return $response;
    }

    /**
     * 构建响应
     *
     * @param RouteMatcher $matcher
     * @param Request $request
     * @param Response $response
     * @param string $name
     * @param array $parameter
     * @return Response
     */
    protected function buildResponse(RouteMatcher $matcher, Request $request, Response $response, string $name, array  $parameter):Response
    {
        foreach ($parameter as $key => $value) {
            $request->setQuery($key, $value);
        }
        $request->setAttributes($matcher->getAttribute());
        $content = $this->runnable[$name]->run($request, $response);
        if ($content != null) {
            $response->setContent($content);
        }
        return $response;
    }

    /**
     * 创建默认运行器
     *
     * @return Runnable
     */
    protected function createDefaultRunnable():Runnable
    {
        return new Runnable(function (Request $request, Response $response) {
            $response->status(404);
            $response->setType('html');
            return 'Page Not Found: '.$request->getUrl();
        });
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
