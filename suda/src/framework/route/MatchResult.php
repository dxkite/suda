<?php
namespace suda\framework\route;



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
     * 构建结果
     *
     * @param RouteMatcher $matcher
     * @param string $name
     * @param array $parameter
     */
    public function __construct(RouteMatcher $matcher, string $name, array  $parameter) {
        $this->matcher = $matcher;
        $this->name = $name;
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
}
