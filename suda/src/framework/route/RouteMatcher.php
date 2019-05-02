<?php
namespace suda\framework\route;

use function in_array;
use suda\framework\Request;
use suda\framework\route\uri\UriMatcher;

/**
 * 路由匹配器
 *
 */
class RouteMatcher
{
    
    /**
     * 匹配的方法
     *
     * @var array
     */
    protected $methods;

    /**
     * 匹配的URI
     *
     * @var string
     */
    protected $uri;

    /**
     * 属性
     *
     * @var array
     */
    protected $attribute;

    /**
     * Uri匹配器
     *
     * @var UriMatcher
     */
    protected $matcher;
    

    public function __construct(array $methods, string $uri, array $attribute = [])
    {
        array_walk($methods, function ($value) {
            return strtoupper($value);
        });
        $this->methods = $methods;
        $this->uri = $uri;
        $this->matcher = UriMatcher::build($uri);
        $this->attribute = $attribute;
    }
    
    /**
     * 获取属性
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getAttribute(string $name = null, $default = null)
    {
        if (is_string($name)) {
            return $this->attribute[$name] ?? $default;
        }
        return $this->attribute;
    }

    /**
     * Set 属性
     *
     * @param  array  $attribute  属性
     *
     * @return  self
     */
    public function setAttribute(array $attribute)
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * 添加属性
     *
     * @param string $name
     * @param mixed $attribute
     * @return $this
     */
    public function addAttribute(string $name, $attribute)
    {
        $this->attribute[$name] = $attribute;

        return $this;
    }

    /**
     * Get 匹配的方法
     *
     * @return  array
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * Set 匹配的方法
     *
     * @param  array  $methods  匹配的方法
     *
     * @return  self
     */
    public function setMethods(array $methods)
    {
        $this->methods = $methods;

        return $this;
    }

    /**
     * Get 匹配的URI
     *
     * @return  string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Set 匹配的URI
     *
     * @param  string  $uri  匹配的URI
     *
     * @return  self
     */
    public function setUri(string $uri)
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * Get uri匹配器
     *
     * @return  UriMatcher
     */
    public function getMatcher()
    {
        return $this->matcher;
    }

    /**
     * 匹配请求
     *
     * @param Request $request
     * @return array|null
     */
    public function match(Request $request):?array
    {
        if (count($this->methods) > 0 && !in_array($request->getMethod(), $this->methods)) {
            return null;
        }
        if (($parameter = $this->matcher->match($request->getUri())) !== null) {
            return $this->matcher->buildParamter($parameter);
        }
        return null;
    }
}
