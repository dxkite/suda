<?php
namespace suda\framework\server\response;

use suda\framework\server\response\Status;

/**
 * HTTP 入口解析查找
 */
class Header
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
     * @param string $name
     * @param string $content
     * @return self
     */
    public function add(string $name, string $content)
    {
        $this->header[$name][] = $content;
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
     * 发送头部信息
     *
     * @return self
     */
    public function sendHeaders(int $statusCode)
    {
        if (\headers_sent()) {
            return $this;
        }
        foreach ($this->header as $name => $values) {
            foreach ($values as $value) {
                \header(\sprintf('%s: %s', $name, $value), false, $statusCode);
            }
        }
        header(sprintf('HTTP/1.1 %s %s', $statusCode, Status::toText($statusCode)), true, $statusCode);
        return $this;
    }
}
