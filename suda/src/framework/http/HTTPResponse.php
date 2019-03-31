<?php
namespace suda\framework\http;

use suda\framework\http\Cookie;
use suda\framework\http\Header;
use suda\framework\http\Status;
use suda\framework\http\Stream;
use suda\framework\http\Response;
use suda\framework\http\HeaderContainer;
use suda\framework\http\stream\DataStream;

/**
 * 原始HTTP响应
 */
class HTTPResponse implements Response 
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
     * @var \suda\framework\http\Stream|string|array
     */
    protected $data;

    /**
     * Cookie代码
     *
     * @var \suda\framework\http\Cookie[]
     */
    protected $cookie;

    /**
     * 是否发送
     *
     * @var boolean
     */
    protected $sended = false;

    public function __construct()
    {
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
     * 判断是否发送
     *
     * @return boolean
     */
    public function isSended(): bool
    {
        return $this->sended;
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
     * 写数据
     *
     * @param Stream|string $data
     * @return void
     */
    public function write($data)
    {
        $this->data[] = $data;
    }

    /**
     * 发送数据
     *
     * @param Stream|string $data
     * @return void
     */
    public function send($data)
    {
        if (\is_array($this->data)) {
            $this->data[] = $data;
        } else {
            $this->data = $data;
        }
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
        $this->data = new DataStream($filename, $offset, $length);
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
        $this->sendCookies();
        $this->sendHeaders();
        $this->sendData();
        $this->sended = true;
    }

    /**
     * 发送头部信息
     *
     * @return self
     */
    protected function sendHeaders()
    {
        if ($this->sended) {
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
        if ($this->sended) {
            return $this;
        }
        foreach ($this->cookie as $cookie) {
            $cookie->send($this);
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
        if (is_array($this->data)) {
            $this->sendArrayData($this->data);
        } else {
            $this->echoDataContent($this->data);
        }
    }

    /**
     * 发送数据
     *
     * @param array $data
     * @return void
     */
    protected function sendArrayData(array $data)
    {
        foreach ($data as $content) {
            $this->echoDataContent($content);
        }
    }

    /**
     * 发送数据内容
     *
     * @param Stream|string $data
     * @return void
     */
    protected function echoDataContent($data)
    {
        if ($data instanceof Stream) {
            $data->echo();
        } else {
            echo $data;
        }
    }
}


