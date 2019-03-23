<?php
namespace suda\framework\route;

use suda\framework\runnable\Runnable;



/**
 * 匹配结果
 *
 */
class MatchResult
{
    /**
     * 匹配工具
     *
     * @var RouteMatcher
     */
    protected $matcher;
    
    /**
     * 路由名
     *
     * @var string
     */
    protected $name;

    /**
     * 路由参数
     *
     * @var array
     */
    protected $parameter;
    
    /**
     * 可执行对象
     *
     * @var Runnable
     */
    protected $runnable;

    /**
     * 构建结果
     *
     * @param RouteMatcher $matcher
     * @param string $name
     * @param array $parameter
     */
    public function __construct(RouteMatcher $matcher, string $name, Runnable $runnable, array  $parameter) {
        $this->matcher = $matcher;
        $this->name = $name;
        $this->runnable = $runnable;
        $this->parameter = $parameter;
    }

    /**
     * Get 路由参数
     *
     * @return  array
     */ 
    public function getParameter()
    {
        return $this->parameter;
    }

    /**
     * Get 路由名
     *
     * @return  string
     */ 
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get 匹配工具
     *
     * @return  RouteMatcher
     */ 
    public function getMatcher()
    {
        return $this->matcher;
    }

    /**
     * Get 可执行对象
     *
     * @return  Runnable
     */ 
    public function getRunnable()
    {
        return $this->runnable;
    }

    /**
     * Set 可执行对象
     *
     * @param  Runnable  $runnable  可执行对象
     *
     * @return  self
     */ 
    public function setRunnable(Runnable $runnable)
    {
        $this->runnable = $runnable;

        return $this;
    }
}
