<?php
namespace suda\framework\http;

use Exception;
use function header as send_header;
use suda\framework\http\stream\DataStream;

/**
 * 原始HTTP响应
 */
class HTTPResponse implements Response
{
    /**
     * 头部代码
     *
     * @var HeaderContainer
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
     * Cookie代码
     *
     * @var Cookie[]
     */
    protected $cookie;

    /**
     * 是否发送
     *
     * @var boolean
     */
    protected $send = false;

    public function __construct()
    {
        $this->cookie = [];
        $this->header = new HeaderContainer;
    }

    /**
     * 设置状态码
     *
     * @param integer $statusCode
     * @return $this
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
     * @return $this
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
    public function isSend(): bool
    {
        return $this->send || headers_sent();
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
        $this->header->add(new Header($name, $value, $ucfirst), $replace);
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
        if (!in_array($cookie, $this->cookie)) {
            $this->cookie[] = $cookie;
        }
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
        $this->sendHeaders();
        $this->writeOutput($data);
    }

    /**
     * 发送数据
     *
     * @param Stream|string $data
     * @return void
     */
    public function send($data)
    {
        $this->sendHeaders();
        $this->writeOutput($data);
        $this->closeConnection();
    }

    /**
     * 发送文件内容
     *
     * @param string $filename
     * @param integer $offset
     * @param integer $length
     * @return void
     * @throws Exception
     */
    public function sendFile(string $filename, int $offset = 0, int $length = null)
    {
        if (!file_exists($filename)) {
            throw new Exception('file no found: '.$filename);
        }
        $data = new DataStream($filename, $offset, $length);
        $this->sendHeaders();
        $this->writeOutput($data);
        $this->closeConnection();
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
    }

    /**
     * 请求结束处理
     *
     * @return void
     */
    public function end()
    {
        $this->sendHeaders();
        $this->closeConnection();
        $this->send = true;
    }

    /**
     * @return $this
     */
    protected function sendHeaders()
    {
        if ($this->isSend()) {
            return $this;
        }
        $this->prepareCookieHeader();
        $this->sendHeader();
        $this->send = true;
        return $this;
    }

    /**
     * 发送头部信息
     *
     * @return $this
     */
    private function sendHeader()
    {
        foreach ($this->header->all() as $name => $values) {
            foreach ($values as $header) {
                send_header($header, false, $this->status);
            }
        }
        send_header(sprintf(
            'HTTP/%s %s %s',
            $this->version,
            $this->status,
            Status::toText($this->status)
        ), true, $this->status);
        return $this;
    }

    /**
     * 准备Cookie头
     *
     * @return $this
     */
    private function prepareCookieHeader()
    {
        foreach ($this->cookie as $cookie) {
            $cookie->send($this);
        }
        return $this;
    }

    /**
     * 关闭所有缓存
     *
     * @param boolean $flush
     * @return void
     */
    protected function closeOutputBuffer(bool $flush)
    {
        while (ob_get_level() > 0) {
            $flush?ob_end_flush():ob_end_clean();
        }
    }

    /**
     * 发送数据内容
     *
     * @param Stream|string $data
     * @return void
     */
    protected function writeOutput($data)
    {
        $this->closeOutputBuffer(false);
        if ($data instanceof Stream) {
            $data->echo();
        } else {
            echo $data;
        }
    }

    protected function closeConnection()
    {
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
    }
}
