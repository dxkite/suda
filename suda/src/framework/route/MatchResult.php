<?php
namespace suda\framework\route;

use suda\framework\Request;
use suda\framework\Response;
use suda\framework\runnable\Runnable;
use suda\framework\route\RouteMatcher;

/**
 * 匹配结果
 */
class MatchResult
{
    /**
     * 匹配对象
     *
     * @var RouteMatcher
     */
    protected $matcher;


    /**
     * 匹配的参数
     *
     * @var array
     */
    protected $parameter;

    /**
     * 匹配的名字
     *
     * @var string
     */
    protected $name;

    /**
     * 可执行对象
     *
     * @var Runnable
     */
    protected $runnable;

    /**
     * 构建匹配结果
     *
     * @param string $name
     * @param RouteMatcher $matcher
     * @param Runnable $runnable
     * @param array $parameter
     */
    public function __construct(string $name, RouteMatcher $matcher, Runnable $runnable, array $parameter)
    {
        $this->matcher = $matcher;
        $this->parameter = $parameter;
        $this->name = $name;
        $this->runnable = $runnable;
    }

    /**
     * Get 匹配对象
     *
     * @return  RouteMatcher
     */
    public function getMatcher()
    {
        return $this->matcher;
    }

    /**
     * Set 匹配对象
     *
     * @param  RouteMatcher  $matcher  匹配对象
     *
     * @return  self
     */
    public function setMatcher(RouteMatcher $matcher)
    {
        $this->matcher = $matcher;

        return $this;
    }

    /**
     * Get 匹配的参数
     *
     * @return  array
     */
    public function getParameter()
    {
        return $this->parameter;
    }

    /**
     * Set 匹配的参数
     *
     * @param  array  $parameter  匹配的参数
     *
     * @return  self
     */
    public function setParameter(array $parameter)
    {
        $this->parameter = $parameter;

        return $this;
    }

    /**
     * Get 匹配的名字
     *
     * @return  string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set 匹配的名字
     *
     * @param  string  $name  匹配的名字
     *
     * @return  self
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * 运行程序
     *
     * @param Request $request
     * @param Response $response
     * @return void
     */
    public function run(Request $request, Response $response)
    {
        foreach ($this->parameter as $key => $value) {
            $request->setQuery($key, $value);
        }
        $result = $this->runnable->run($request, $response);
        if (!$response->isSended()) {
            $response->sendContent($result);
        }
    }
}
