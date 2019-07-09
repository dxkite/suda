<?php
namespace suda\framework\route\uri;

use suda\framework\route\uri\parameter\Parameter;

/**
 * 路由匹配工具
 */
class UriMatcher extends MatcherHelper
{

    /**
     * 匹配Uri
     *
     * @var string
     */
    protected $uri;
    
    /**
     * 匹配的正则
     *
     * @var string
     */
    protected $match;

    /**
     * URI中的参数
     *
     * @var Parameter[]
     */
    protected $parameter;
    
    
    public function __construct(string $uri, string $match, array $parameter)
    {
        $this->uri = $uri;
        $this->match = $match;
        $this->parameter = $parameter;
    }

    /**
     * 匹配URL
     *
     * @param string $url
     * @param boolean $ignoreCase
     * @return array|null
     */
    public function match(string $url, bool $ignoreCase = true):?array
    {
        $match = '#^'. $this->match.'$#'. ($ignoreCase?'i':'');
        $parameter = [];
        if (preg_match($match, $url, $parameter, PREG_UNMATCHED_AS_NULL) > 0) {
            return array_slice($parameter, 1);
        }
        return null;
    }

    /**
     * 构建参数
     *
     * @param array $matchedParameter
     * @return array
     */
    public function buildParameter(array $matchedParameter):array
    {
        $parameters = [];
        foreach ($this->parameter as $index => $parameter) {
            $match = $matchedParameter[$index] ?? null;
            if (null === $match) {
                $value = $parameter->getDefaultValue();
            } else {
                $value = $parameter->unpackValue($match);
            }
            if ($value !== null) {
                $parameters[$parameter->getIndexName()] = $value;
            }
        }
        return $parameters;
    }

    public function getParameter(string $name):?Parameter
    {
        foreach ($this->parameter as $parameter) {
            if ($parameter->getIndexName() === $name) {
                return $parameter;
            }
        }
        return null;
    }

    /**
     * Get 匹配的正则
     *
     * @return  string
     */
    public function getMatch()
    {
        return $this->match;
    }

    /**
     * Set 匹配的正则
     *
     * @param  string  $match  匹配的正则
     *
     * @return  self
     */
    public function setMatch(string $match)
    {
        $this->match = $match;

        return $this;
    }

    /**
     * Get 匹配Uri
     *
     * @return  string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * 获取参数
     *
     * @param integer $index
     * @return Parameter|null
     */
    public function getParameterByIndex(int $index):?Parameter
    {
        foreach ($this->parameter as $parameter) {
            if ($parameter->getIndex() === $index) {
                return $parameter;
            }
        }
        return null;
    }
}
