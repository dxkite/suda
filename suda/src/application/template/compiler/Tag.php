<?php

namespace suda\application\template\compiler;

use Exception;
use function str_replace;

class Tag implements EchoValueInterface
{
    use EchoValueTrait;

    protected $content;

    /**
     * 配置信息
     *
     * @var array
     */
    protected $config;
    /**
     * 标签名
     *
     * @var string
     */
    protected $name;
    /**
     * 开标签
     *
     * @var string
     */
    protected $open;
    /**
     * 闭标签
     *
     * @var string
     */
    protected $close;

    public function __construct(string $name, string $open, string $close, string $content)
    {
        $this->content = $content;
        $this->name = $name;
        $this->open = $open;
        $this->close = $close;
    }

    /**
     * @param string $content
     * @return string
     * @throws Exception
     */
    public function compile(string $content): string
    {
        return $this->parseEchoValue(str_replace('$code', $content, $this->content));
    }

    /**
     * Get 开标签
     *
     * @return  string
     */
    public function getOpen()
    {
        return $this->open;
    }

    /**
     * Get 闭标签
     *
     * @return  string
     */
    public function getClose()
    {
        return $this->close;
    }

    /**
     * Set 闭标签
     *
     * @param string $close 闭标签
     *
     * @return  self
     */
    public function setClose(string $close)
    {
        $this->close = $close;

        return $this;
    }

    /**
     * Set 开标签
     *
     * @param string $open 开标签
     *
     * @return  self
     */
    public function setOpen(string $open)
    {
        $this->open = $open;

        return $this;
    }

    /**
     * Get 标签名
     *
     * @return  string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set 标签名
     *
     * @param string $name 标签名
     *
     * @return  self
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set 配置信息
     *
     * @param array $config 配置信息
     *
     * @return  self
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
        if (is_numeric(key($config))) {
            if (array_key_exists(0, $config)) {
                $this->setOpen($config[0]);
            }
            if (array_key_exists(1, $config)) {
                $this->setClose($config[1]);
            }
        } else {
            if (array_key_exists('open', $config)) {
                $this->setOpen($config['open']);
            }
            if (array_key_exists('close', $config)) {
                $this->setClose($config['close']);
            }
        }
        return $this;
    }
}
