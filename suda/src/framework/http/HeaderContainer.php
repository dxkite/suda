<?php
namespace suda\framework\http;

use suda\framework\http\Header;

/**
 * 响应头
 */
class HeaderContainer
{
    /**
     * 响应头
     *
     * @var array
     */
    protected $header;

    /**
     * 添加请求头
     *
     * @param \suda\framework\http\Header $header
     * @param boolean $replace
     * @return self
     */
    public function add(Header $header, bool $replace = false)
    {
        if ($replace) {
            $this->header[$name] = [];
        }
        $this->header[$name][] = $header;
        return $this;
    }

    /**
     * 获取头部
     *
     * @param string $name
     * @param mixed $default
     * @param boolean $first
     * @return string|null|string[]
     */
    public function get(string $name, $default = null, bool $first = true)
    {
        if ($first) {
            return $this->header[$name][0] ?? $default;
        }
        return $this->header[$name] ?? $default;
    }

    /**
     * 检测是否包含头部
     *
     * @param string $name
     * @return boolean
     */
    public function has(string $name) : bool
    {
        return \array_key_exists($name, $this->header);
    }


    /**
     * 删除头部
     *
     * @param string $key
     * @return self
     */
    public function remove(string $name)
    {
        unset($this->header[$name]);
        return $this;
    }

    /**
     * 获取全部
     *
     * @return array
     */
    public function all():string {
        return $this->header;
    }
}
