<?php
namespace suda\framework;

use suda\framework\Request;
use suda\framework\http\Cookie;
use suda\framework\http\Header;
use suda\framework\response\MimeType;
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
     * @return self
     */
    public function setHeader(string $name, string $content)
    {
        $this->header->add(new Header($name, $content));
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
        $this->setHeader('content-length', is_string($this->data) ? strlen($this->data) : $this->data->length());
        return $this;
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
        $this->end();
    }
}
