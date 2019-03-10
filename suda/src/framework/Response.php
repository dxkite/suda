<?php
namespace suda\framework;

use SplFileInfo;
use suda\framework\Request;
use suda\framework\http\Cookie;
use suda\framework\http\Header;
use suda\framework\http\Stream;
use suda\framework\response\MimeType;
use suda\framework\http\stream\DataStream;
use suda\framework\response\ContentWrapper;
use suda\framework\http\Response as HTTPResponse;

class Response extends HTTPResponse
{
    /**
     * 设置类型
     *
     * @param string $extension
     * @return self
     */
    public function setType(string $extension)
    {
        $this->header->add(new Header('content-type', MimeType::getMimeType($extension)), true);
        return $this;
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
        $this->header->add(new Header($name, $content), $replace);
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
        $wrapper = ContentWrapper::getWrapper($content);
        $this->data = $wrapper->getContent($this);
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
        $content = new  SplFileInfo($filename);
        if ($content->isFile()) {
            $this->setType($content->getExtension());
            $this->setHeader('Content-Disposition', 'attachment;filename="' . $content->getBasename().'"');
            $this->setHeader('Cache-Control', 'max-age=0');
            $this->data = new DataStream($content->getRealPath(), $offset, $length);
        } else {
            throw new \Exception('sendFile must be file');
        }
        $this->end();
    }

    /**
     * 发送内容数据
     *
     * @param array|string|Stream $data
     * @return void
     */
    protected function sendContentLength($data)
    {
        if (is_array($data)) {
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
    * @param  Cookie  $cookie  Cookie
    *
    * @return  self
    */
    public function setCookie(Cookie $cookie)
    {
        $this->cookie[$cookie->getName()] = $cookie;
        return $this;
    }

    /**
     * 获取Cookie
     *
     * @param string $name
     * @return Cookie|null
     */
    public function getCookie(string $name):?Cookie
    {
        return $this->cookie[$name] ?? null;
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
        $this->end();
    }
}
