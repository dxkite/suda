<?php


namespace suda\framework\response;

use suda\framework\http\Cookie;
use suda\framework\http\Response;
use suda\framework\http\Stream;

class ResponseWrapper implements Response
{
    /**
     * @var Response
     */
    protected $response;

    /**
     * @varint
     */
    protected $status = 200;

    /**
     * 创建响应
     * @param Response $response
     */
    public function __construct(Response $response)
    {
        $this->status = 200;
        $this->response = $response;
    }

    /**
     * 设置状态码
     *
     * @param integer $statusCode
     * @return Response
     */
    public function status(int $statusCode)
    {
        $this->status = $statusCode;
        $this->response->status($statusCode);
        return $this;
    }


    /**
     * 获取状态码
     *
     * @return integer
     */
    public function getStatus():int
    {
        return $this->status;
    }


    /**
     * 设置响应版本
     *
     * @param string $version
     * @return $this
     */
    public function version(string $version)
    {
        $this->response->version($version);
        return $this;
    }

    /**
     * 判断是否发送
     *
     * @return boolean
     */
    public function isSend(): bool
    {
        return $this->response->isSend();
    }

    /**
     * 设置头部信息
     *
     * @param string $name
     * @param string $value
     * @param bool $replace
     * @param bool $ucfirst
     * @return $this
     */
    public function header(string $name, string $value, bool $replace = false, bool $ucfirst = true)
    {
        $this->response->header($name, $value, $replace, $ucfirst);
        return $this;
    }

    /**
     * 设置Cookie信息
     *
     * @param Cookie $cookie
     * @return $this
     */
    public function cookie(Cookie $cookie)
    {
        $this->response->cookie($cookie);
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
        $this->response->send($data);
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
        $this->response->redirect($url, $httpCode);
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
        $this->response->sendFile($filename, $offset, $length);
    }
}
