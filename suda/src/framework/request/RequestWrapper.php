<?php
namespace suda\framework\request;

use suda\framework\http\Request;
use suda\framework\http\UploadedFile;
use suda\framework\request\IndexFinder;


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
     * 创建请求包装器
     *
     * @param Request $request
     */
    public function __construct(Request $request) {
        $this->request = $request;
        $this->setRemoteAddr($this->filterRemoteAddr());
        $this->setMethod(strtoupper($request->server['request-method'] ?? 'GET'));
        $this->setHost($this->getHttpHost());
        $this->setSecure($this->getSecure());
        $this->setPort($this->getServerPort());
        $this->createUri();
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
     * @param  string  $remoteAddr  远程地址
     *
     * @return  self
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
     * @param  string  $host  获取本地HOST
     *
     * @return  self
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
     * @param  int  $port  获取本地端口
     *
     * @return  self
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
     * @param  bool  $secure  是否为安全模式
     *
     * @return  self
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
     * @param  string  $uri  请求URI
     *
     * @return  self
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
     * @param  string  $method  请求参数
     *
     * @return  self
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
     * @param mixed $parameter
     * @return self
     */
    public function setQuery(string $name, $query)
    {
        $this->query[$name] = $query;

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
     * @param  string  $index  请求索引
     *
     * @return  self
     */
    public function setIndex(string $index)
    {
        $this->index = $index;

        return $this;
    }

    /**
     * 获取文件
     *
     * @return  UploadedFile[]|UploadedFile|null
     */
    public function getFile(?string $name = null)
    {
        return null === $name ? $this->request->files : $this->request->files[$name] ?? null;
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
        if (array_key_exists(strtolower($name), $this->request->header)) {
            return $this->request->header[$name];
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
        return $this->request->files;
    }

    /**
     * Set 文件包装
     *
     * @param  UploadedFile[]  $files  文件包装
     *
     * @return  self
     */
    public function setFiles(array $files)
    {
        $this->request->files = $files;

        return $this;
    }

    /**
     * Get 请求头部
     *
     * @return  string[]
     */
    public function getHeaders()
    {
        return $this->request->header;
    }

    /**
     * Set 请求头部
     *
     * @param  string[]  $headers  请求头部
     *
     * @return  self
     */
    public function setHeaders(array $headers)
    {
        $this->request->header = $headers;

        return $this;
    }

    /**
     * 获取IP地址
     *
     * @return string
     */
    private function filterRemoteAddr():string
    {
        static $ipFrom = [
            'http-client-ip', 
            'http-x-forwarded-for', 
            'http-x-forwarded',
            'http-x-cluster-client-ip', 
            'http-forwarded-for', 
            'http-forwarded',
            'remote-addr',
        ];
        foreach ($ipFrom as $key) {
            if (array_key_exists($key, $this->request->server)) {
                foreach (explode(',', $this->request->server[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return  '0.0.0.0';
    }
    
    /**
     * 从请求投中获取HOST
     *
     * @return string
     */
    private function getHttpHost():string
    {
        if (array_key_exists('host', $this->request->header)) {
            return explode(':', $this->request->header['host'])[0];
        }
        return $this->request->server['server-name'] ?? 'localhost';
    }

    /**
     * 获取端口
     *
     * @return integer
     */
    private function getServerPort():int
    {
        if (array_key_exists('server-port', $this->request->server)) {
            return $this->request->server['server-port'];
        }
        return $this->getSecure()?443:80;
    }

    /**
     * 获取安全状态
     *
     * @return boolean
     */
    private function getSecure():bool
    {
        $https = array_key_exists('https', $this->request->server) && strcasecmp($this->request->server['https'], 'off') != 0;
        $scheme = array_key_exists('request-scheme', $this->request->server) && strcasecmp($this->request->server['request-scheme'], 'https') === 0;
        return $https || $scheme;
    }

    /**
     * 创建URI
     *
     * @return void
     */
    private function createUri()
    {
        $index = (new IndexFinder(null, $this->request->server['document-root']))->getIndexFile();
        $this->setIndex($index);
        $url = new UriParser($this->request->server['request-uri'] ?? '/', $index);
        $this->query =  $url->getQuery();
        $this->setUri($url->getUri());
        $this->request->get = $url->getQuery();
    }
}
