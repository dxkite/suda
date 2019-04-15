<?php
namespace suda\framework;

use SplFileObject;
use suda\framework\Request;
use suda\framework\http\Cookie;
use suda\framework\http\Header;
use suda\framework\http\Stream;
use suda\framework\response\MimeType;
use suda\framework\http\stream\DataStream;
use suda\framework\response\ContentWrapper;
use suda\framework\http\HTTPResponse;

class Response extends HTTPResponse
{
    /**
     * 包装器
     *
     * @var ContentWrapper
     */
    protected $wrapper;

    /**
     * 响应数据
     *
     * @var \suda\framework\http\Stream|string
     */
    protected $data;

    /**
     * 创建响应
     */
    public function __construct()
    {
        parent::__construct();
        $this->wrapper = new ContentWrapper;
    }

    /**
     * 设置类型
     *
     * @param string $extension
     * @return self
     */
    public function setType(string $extension)
    {
        $this->header('content-type', MimeType::getMimeType($extension), true);
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
     * 设置头部
     *
     * @param string $name
     * @param string $content
     * @param boolean $replace
     * @return self
     */
    public function setHeader(string $name, string $content, bool $replace = false)
    {
        $this->header($name, $content, $replace);
        return $this;
    }

    /**
     * 设置请求内容
     *
     * @param mixed $content
     * @return self
     */
    public function setContent($content)
    {
        if (\is_string($content) || $content instanceof Stream) {
            $this->data = $content;
        } else {
            $wrapper = $this->wrapper->getWrapper($content);
            $this->data = $wrapper->getContent($this);
        }
        return $this;
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
        $content = new  SplFileObject($filename);
        if ($content->isFile()) {
            $this->setType($content->getExtension());
            $this->data = new DataStream($content->getRealPath(), $offset, $length);
        } else {
            throw new \Exception('sendFile must be file');
        }
        $this->sendContentLength($this->data);
        $this->end();
    }


    /**
     * 结束请求
     *
     * @return void
     */
    public function end()
    {
        $this->sendContent($this->data);
    }

    /**
     * 发送内容数据
     *
     * @param array|string|Stream|null $data
     * @return self
     */
    protected function sendContentLength($data)
    {
        if ($data === null) {
            $this->setHeader('content-length', 0);
        } elseif (is_array($data)) {
            $this->setHeader('content-length', $this->getDataLengthArray($data), true);
        } else {
            $this->setHeader('content-length', $this->getDataLengthItem($data), true);
        }
        return $this;
    }

    /**
     * 获取数据长度
     *
     * @param Stream[] $data
     * @return int
     */
    protected function getDataLengthArray(array $data):int
    {
        $length = 0;
        foreach ($data as $item) {
            $length += $this->getDataLengthItem($item);
        }
        return $length;
    }

    /**
     * 获取数据长度
     *
     * @param Stream|string $data
     * @return integer
     */
    protected function getDataLengthItem($data):int
    {
        if (is_string($data)) {
            return strlen($data);
        } else {
            return $data->length();
        }
    }

    /**
     * 设置 Cookie
     *
     * @param string $name
     * @param string $value
     * @param integer $expire
     * @return \suda\framework\http\Cookie
     */
    public function setCookie(string $name, string $value, int $expire = 0):Cookie
    {
        $cookie = new Cookie($name, $value, $expire);
        $this->cookie($cookie);
        return $cookie;
    }

    /**
     * 发送数据
     *
     * @param mixed $content
     * @return void
     */
    public function sendContent($content = null)
    {
        if ($content !== null) {
            $this->setContent($content);
        }
        $this->sendContentLength($this->data);
        $this->send($this->data);
    }

    /**
     * Get 包装器
     *
     * @return  ContentWrapper
     */
    public function getWrapper()
    {
        return $this->wrapper;
    }

    /**
     * Set 包装器
     *
     * @param  ContentWrapper  $wrapper  包装器
     *
     * @return  self
     */
    public function setWrapper(ContentWrapper $wrapper)
    {
        $this->wrapper = $wrapper;

        return $this;
    }
}
