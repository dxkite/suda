<?php


namespace suda\swoole;

use suda\framework\http\Cookie;
use suda\framework\http\Stream;

/**
 * Class Response
 * @package suda\swoole
 */
class Response implements \suda\framework\http\Response
{
    /**
     * @var bool
     */
    protected $send = false;

    /**
     * @var \Swoole\Http\Response
     */
    protected $response;

    /**
     * @var int
     */
    protected $status = 200;

    /**
     * Response constructor.
     * @param \Swoole\Http\Response $response
     */
    public function __construct(\Swoole\Http\Response $response)
    {
        $this->response = $response;
        $this->send = false;
    }


    /**
     * 设置状态码
     *
     * @param integer $statusCode
     * @return \suda\framework\http\Response
     */
    public function status(int $statusCode)
    {
        $this->response->status($statusCode);
        $this->status  = $statusCode;
        return $this;
    }

    /**
     * 判断是否发送
     *
     * @return boolean
     */
    public function isSend(): bool
    {
        return $this->send;
    }

    /**
     * 设置头部信息
     *
     * @param string $name
     * @param string $value
     * @param bool $replace
     * @param bool $ucfirst
     * @return \suda\framework\http\Response
     */
    public function header(string $name, string $value, bool $replace = false, bool $ucfirst = true)
    {
        $this->response->header($name, $value, $ucfirst);
        return $this;
    }

    /**
     * 设置Cookie信息
     *
     * @param Cookie $cookie
     * @return \suda\framework\http\Response
     */
    public function cookie(Cookie $cookie)
    {
        $this->response->cookie(
            $cookie->getName(),
            $cookie->getValue(),
            $cookie->getExpire(),
            $cookie->getPath(),
            $cookie->getDomain(),
            $cookie->isSecure()
        );
        return $this;
    }

    /**
     * 写数据
     *
     * @param Stream|string $data
     * @return void
     */
    public function write($data)
    {
        $this->response->write($data);
    }

    /**
     * 发送数据
     *
     * @param Stream|string $data
     * @return void
     */
    public function send($data)
    {
        $this->send = true;
        $this->response->end($data);
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
        $this->send = true;
        $this->response->sendfile($filename, $offset, $length);
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
        $this->send = true;
        $this->response->redirect($url, $httpCode);
    }

    /**
     * 设置响应版本
     *
     * @param string $version
     * @return \suda\framework\http\Response
     */
    public function version(string $version)
    {
        return $this;
    }
}
