<?php
namespace suda\framework\request;

use suda\framework\http\Request;
use suda\framework\http\UploadedFile;

/**
 * 请求包装器
 * 包装PHP请求
 */
class RequestWrapper
{

    /**
     * HTTP请求
     *
     * @var Request
     */
    protected $request;
    /**
     * 远程地址
     *
     * @var string
     */
    protected $remoteAddr = '0.0.0.0';

    /**
     * 获取本地HOST
     *
     * @var string
     */
    protected $host = '127.0.0.1';

    /**
     * 获取本地端口
     *
     * @var int
     */
    protected $port = 80;

    /**
     * 是否为安全模式
     *
     * @var boolean
     */
    protected $secure = false;

    /**
     * 请求URI
     *
     * @var string
     */
    protected $uri = '/';

    /**
     * 请求参数
     *
     * @var string
     */
    protected $method = 'GET';

    /**
     * 查询参数($_GET)
     *
     * @var array
     */
    protected $query = [];

    /**
     * 请求索引
     *
     * @var string
     */
    protected $index;

    /**
     * URI基础部分
     *
     * @var string
     */
    protected $uriBase;

    /**
     * 服务器环境信息
     *
     * @var array
     */
    protected $server;

    /**
     * 创建请求包装器
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        (new Builder($request))->build($this);
    }

    /**
     * Get 远程地址
     *
     * @return  string
     */
    public function getRemoteAddr()
    {
        return $this->remoteAddr;
    }

    /**
     * Set 远程地址
     *
     * @param string $remoteAddr 远程地址
     *
     * @return  $this
     */
    public function setRemoteAddr(string $remoteAddr)
    {
        $this->remoteAddr = $remoteAddr;

        return $this;
    }

    /**
     * Get 获取本地HOST
     *
     * @return  string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set 获取本地HOST
     *
     * @param string $host 获取本地HOST
     *
     * @return  $this
     */
    public function setHost(string $host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Get 获取本地端口
     *
     * @return  int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Set 获取本地端口
     *
     * @param int $port 获取本地端口
     *
     * @return  $this
     */
    public function setPort(int $port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * Get 是否为安全模式
     *
     * @return  bool
     */
    public function isSecure():bool
    {
        return $this->secure;
    }

    /**
     * Set 是否为安全模式
     *
     * @param bool $secure 是否为安全模式
     * @return  $this
     */
    public function setSecure(bool $secure)
    {
        $this->secure = $secure;
        return $this;
    }

    /**
     * Get 请求URI
     *
     * @return  string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Set 请求URI
     *
     * @param string $uri 请求URI
     * @return  $this
     */
    public function setUri(string $uri)
    {
        $this->uri = $uri;
        return $this;
    }

    /**
     * Get 请求参数
     *
     * @return  string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set 请求参数
     *
     * @param string $method 请求参数
     * @return  $this
     */
    public function setMethod(string $method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * 获取查询参数
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getQuery(?string $name = null, $default = null)
    {
        return  $name === null ? $this->query:$this->query[$name] ?? $default;
    }

    /**
     * 设置查询参数
     *
     * @param string $name
     * @param $query
     * @return $this
     */
    public function setQuery(string $name, $query)
    {
        $this->query[$name] = $query;

        return $this;
    }

    /**
     * 设置查询参数
     *
     * @param array $query
     * @return $this
     */
    public function setQueries(array $query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * 合并参数
     *
     * @param array $query
     * @return $this
     */
    public function mergeQueries(array $query)
    {
        $this->query = array_merge($this->query, $query);
        return $this;
    }

    /**
     * Get 请求索引
     *
     * @return  string
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * Set 请求索引
     *
     * @param string $index 请求索引
     * @return $this
     */
    public function setIndex(string $index)
    {
        $this->index = $index;

        return $this;
    }

    /**
     * 获取文件
     *
     * @param string|null $name
     * @return  UploadedFile|null
     */
    public function getFile(string $name)
    {
        return $this->request->files()[$name] ?? null;
    }

    /**
     * 获取请求头
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getHeader(string $name, $default = null)
    {
        if (array_key_exists(strtolower($name), $this->request->header())) {
            return $this->request->header()[$name];
        }
        return $default;
    }

    /**
     * 判断请求头
     *
     * @param string $name
     * @return boolean
     */
    public function hasHeader(string $name)
    {
        return $this->getHeader($name) !== null;
    }

    /**
     * Get 文件包装
     *
     * @return  UploadedFile[]
     */
    public function getFiles()
    {
        return $this->request->files();
    }

    /**
     * Get 请求头部
     *
     * @return  string[]
     */
    public function getHeaders()
    {
        return $this->request->header();
    }

    /**
     * 获取Cookie
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getCookie(string $name = null, $default = null)
    {
        if ($name === null) {
            return $this->request->cookies();
        }
        return $this->request->cookies()[$name] ?? $default;
    }

    /**
     * 获取服务器变量
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getServer(string $name = null, $default = null)
    {
        if ($name === null) {
            return $this->server;
        }
        return $this->server[$name] ?? $default;
    }

    /**
     * Get URI基础部分
     * @return  string
     */
    public function getUriBase()
    {
        return $this->uriBase;
    }

    /**
     * @param array $server
     */
    public function setServer(array $server): void
    {
        $this->server = $server;
    }

    /**
     * Set URI基础部分
     *
     * @param string $uriBase URI基础部分
     * @return  $this
     */
    public function setUriBase(string $uriBase)
    {
        $this->uriBase = $uriBase;
        return $this;
    }
}
