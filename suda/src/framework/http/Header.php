<?php
namespace suda\framework\http;

/**
 * 响应头
 */
class Header
{
    /**
     * 头部名
     *
     * @var string
     */
    protected $name;
    /**
     * 头部值
     *
     * @var string
     */
    protected $value;
    
    /**
     * 是否标准化处理
     *
     * @var boolean
     */
    protected $ucfirst;

    public function __construct(string $name, string $value, bool $ucfirst = true)
    {
        $this->name = \str_replace('_', '-', \strtolower($name));
        $this->value = $value;
        $this->ucfirst = $ucfirst;
    }

    

    /**
     * Get 头部名
     *
     * @return  string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get 头部值
     *
     * @return  string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * 转换成标准头部内容
     *
     * @return string
     */
    public function __toString()
    {
        $name = $this->name;
        if ($this->ucfirst) {
            $names = \explode('-', $name);
            $name = \implode('-', \array_map('ucfirst', $names));
        }
        return \sprintf('%s: %s', $name, $this->value);
    }
}
