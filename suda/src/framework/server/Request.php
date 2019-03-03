<?php
namespace suda\framework\server;

use suda\framework\server\RequestWrapper;
use suda\framework\server\request\UploadedFile;

class Request extends RequestWrapper
{
    /**
     * 参数
     *
     * @var array
     */
    protected $parameter;

    /**
     * 附加属性
     *
     * @var array
     */
    protected $attribute;

    /**
     * 是否为JSON提交
     *
     * @var boolean
     */
    protected $isJson = false;

    /**
     * 获取请求属性
     *
     * @return  mixed
     */
    public function getAttribute(string $name)
    {
        return $this->attribute[$name] ?? null;
    }

    /**
     * 设置请求属性
     *
     * @param string $name
     * @param mixed $attribute
     * @return self
     */
    public function setAttribute(string $name, $attribute)
    {
        $this->attribute[$name] = $attribute;

        return $this;
    }

    /**
     * 获取Scheme
     *
     * @return string
     */
    public function getScheme():string
    {
        return $this->isSecure()?'https':'http';
    }

    /**
     * 判断是否是POST请求
     *
     * @return boolean
     */
    public function isPost():bool
    {
        return $this->getMethod() === 'POST';
    }

    /**
     * 判断是否为GET请求
     *
     * @return boolean
     */
    public function isGet():bool
    {
        return $this->getMethod() === 'GET';
    }
    
    /**
     * 判断内容是否为JSON
     *
     * @return boolean
     */
    public function isJson():bool
    {
        return $this->isJson;
    }

    /**
     * 获取HTTP输入
     *
     * @return string
     */
    public function input():string
    {
        return \file_get_contents('php://input');
    }

    /**
     * 获取JSON数据
     *
     * @return array|null
     */
    public function json():?array
    {
        return $this->isJson?$this->parameter:null;
    }

    /**
     * 获取提交的文件
     *
     * @param string $name
     * @return UploadedFile|null
     */
    public function file(string $name): ?UploadedFile
    {
        return $this->getFile($name);
    }

    /**
     * 判断是否有GET请求
     *
     * @param string|null $name
     * @return boolean
     */
    public function hasGet(?string $name = null)
    {
        $get = $this->getQuery();
        if (null === $name) {
            return \array_key_exists($name, $get);
        }
        return count($get) > 0;
    }

    /**
     * 判断是否有JSON数据请求
     *
     * @return boolean
     */
    public function hasJson()
    {
        if ($this->isJson() && $this->json()) {
            return true;
        }
        return false;
    }

    /**
     * 判断是否有POST数据请求
     *
     * @return boolean
     */
    public function hasPost(?string $name = null)
    {
        $post = $this->post();
        if ($name) {
            return \array_key_exists($name, $post);
        }
        return count($post) > 0;
    }

    /**
     * 获取POST请求的值
     *
     * @param string $name
     * @param mixed $default
     * @return mixed 获取的值
     */
    public function post(?string $name = null, $default = null)
    {
        return $this->getParameter($name, $default);
    }

    /**
     * 获取GET参数
     *
     * @param string|null $name
     * @param mixed $default
     * @return mixed
     */
    public function get(?string $name = null, $default = null)
    {
        return $this->getQuery($name, $default);
    }

    /**
     * 获取Cookie
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function cookie(string $name, $default = null)
    {
        return $_COOKIE[$name] ?? $default;
    }

    /**
     * Get 参数
     *
     * @return  array
     */
    public function getParameter(?string $name = null, $default = null)
    {
        return $name === null ? $this->parameter : $this->parameter[$name] ?? $default;
    }

    /**
     * Set 参数
     *
     * @param  array  $parameter  参数
     *
     * @return  self
     */
    public function setParameter(array $parameter)
    {
        $this->parameter = $parameter;

        return $this;
    }

    /**
     * Set 是否为JSON提交
     *
     * @param  bool  $isJson  是否为JSON提交
     *
     * @return  self
     */
    public function setIsJson(bool $isJson)
    {
        $this->isJson = $isJson;

        return $this;
    }
}
