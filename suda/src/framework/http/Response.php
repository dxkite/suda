<?php
namespace suda\framework\http;

use suda\framework\http\Cookie;
use suda\framework\http\Header;
use suda\framework\http\Stream;
use suda\framework\http\HeaderContainer;

/**
 * 原始HTTP响应
 */
class Response
{
    /**
     * 头部代码
     *
     * @var \suda\framework\http\HeaderContainer
     */
    protected $header;

    /**
     * 状态码
     *
     * @var int
     */
    protected $status = 200;

    /**
     * 响应版本
     *
     * @var string
     */
    protected $version = '1.1';

    /**
     * 响应数据
     *
     * @var string|\suda\framework\http\Stream
     */
    protected $data;

    /**
     * Cookie代码
     *
     * @var \suda\framework\http\Cookie[]
     */
    protected $cookie;

    public function __construct() {
        $this->cookie = [];
        $this->header = new HeaderContainer;
    }

    /**
     * 设置状态码
     *
     * @param integer $statusCode
     * @return self
     */
    public function status(int $statusCode)
    {
        $this->status = $statusCode;
        return $this;
    }

    /**
     * 设置响应版本
     *
     * @param string $version
     * @return self
     */
    public function version(string $version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * 设置头部信息
     *
     * @param string $name
     * @param string $value
     * @param boolean $replace
     * @param boolean $ucfirst
     * @return self
     */
    public function header(string $name, string $value, bool $replace = false, bool $ucfirst = true)
    {
        $this->header->add(new Header($name, $value, $ucfirst), $replace);
        return $this;
    }

    /**
     * 设置Cookie信息
     *
     * @param Cookie $cookie
     * @return self
     */
    public function cookie(Cookie $cookie)
    {
        $this->cookie[$cookie->getName()] = $cookie;
        return $this;
    }

    /**
     * 发送数据
     *
     * @param string $data
     * @return void
     */
    public function send(string $data)
    {
        $this->data = $data;
        $this->end();
    }

    /**
     * 发送文件内容
     *
     * @param string $filename
     * @param integer $offset
     * @param integer $length
     * @return void
     */
    public function sendFile(string $filename, int $offset = 0, int $length = null)
    {
        if (!file_exists($filename)) {
            throw new \Exception('file no found: '.$filename);
        }
        $this->data = new Stream($filename, $offset, $length);
        $this->end();
    }

    /**
     * 跳转
     *
     * @param string $url
     * @param integer $httpCode
     * @return void
     */
    public function redirect(string $url, int $httpCode = 302)
    {
        $this->header('Location', $url);
        $this->status($httpCode);
        $this->end();
    }

    /**
     * 请求结束处理
     *
     * @return void
     */
    protected function end()
    {
        $this->sendHeaders();
        $this->sendCookies();
        $this->sendData();
    }

    /**
     * 发送头部信息
     *
     * @return self
     */
    protected function sendHeaders()
    {
        if (\headers_sent()) {
            return $this;
        }
        foreach ($this->header->all() as $name => $values) {
            foreach ($values as $header) {
                \header($header, false, $this->status);
            }
        }
        header(sprintf('HTTP/%s %s %s', $this->version, $this->status, Status::toText($this->status)), true, $this->status);
        return $this;
    }
    
    /**
     * 发送Cookies
     *
     * @return self
     */
    protected function sendCookies()
    {
        foreach ($this->cookie as $cookie) {
            $cookie->send();
        }
        return $this;
    }

    /**
     * 发送内容
     *
     * @return void
     */
    protected function sendData()
    {
        if (is_string($this->data)) {
            echo $this->data;
        } else {
            $this->data->echo();
        }
    }
}
