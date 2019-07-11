<?php
namespace suda\framework\request;

use function array_key_exists;
use suda\framework\http\Request as RawRequest;

/**
 * 请求包装器
 * 包装PHP请求
 */
class Builder
{

    /**
     * HTTP请求
     *
     * @var RawRequest
     */
    protected $request;

    /**
     * @var array
     */
    protected $server;

    /**
     * 创建请求包装器
     *
     * @param RawRequest $request
     */
    public function __construct(RawRequest $request)
    {
        $this->request = $request;
        $this->server = $this->getFormatServer($request);
    }

    /**
     * 构建对象
     *
     * @param RequestWrapper $request
     * @return void
     */
    public function build(RequestWrapper $request)
    {
        $request->setServer($this->server);
        $request->setRemoteAddr($this->parseRemoteAddr());
        $method = strtoupper($request->getServer('request-method', 'GET'));
        $request->setMethod($method);
        $request->setHost($this->getHttpHost());
        $request->setSecure($this->getSecure());
        $request->setPort($this->getServerPort());
        $this->createUri($request);
        $request->setUriBase(static::createUriBase($request));
    }

    /**
     * 获取IP地址
     *
     * @return string
     */
    private function parseRemoteAddr():string
    {
        static $ipFrom = [
            'http-x-real-ip', // For Nginx
            'http-client-ip',
            'http-x-forwarded-for',
            'http-x-forwarded',
            'http-x-cluster-client-ip',
            'http-forwarded-for',
            'http-forwarded',
            'remote-addr',
        ];

        foreach ($ipFrom as $key) {
            if (array_key_exists($key, $this->server)) {
                $ip = $this->parseIpAddress($this->server[$key]);
                if ($ip !== null) {
                    return $ip;
                }
            }
        }
        return  '0.0.0.0';
    }

    /**
     * 解析IP字段
     * @param string $value
     * @return string|null
     */
    private function parseIpAddress(string $value)
    {
        $ipPart = explode(',', $value);
        $options = FILTER_FLAG_IPV4
            | FILTER_FLAG_IPV6
            | FILTER_FLAG_NO_PRIV_RANGE
            | FILTER_FLAG_NO_RES_RANGE;
        foreach ($ipPart as $ip) {
            $ip = trim($ip);
            if (filter_var($ip, FILTER_VALIDATE_IP, $options) !== false) {
                return $ip;
            }
        }
        return null;
    }
    
    /**
     * 从请求投中获取HOST
     *
     * @return string
     */
    private function getHttpHost():string
    {
        if (array_key_exists('host', $this->request->header())) {
            return explode(':', $this->request->header()['host'])[0];
        }
        return $this->server['server-name'] ?? 'localhost';
    }

    /**
     * 获取端口
     *
     * @return integer
     */
    private function getServerPort():int
    {
        if (array_key_exists('server-port', $this->server)) {
            return $this->server['server-port'];
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
        $https = array_key_exists('https', $this->server)
            && strcasecmp($this->server['https'], 'off') != 0;
        $scheme = array_key_exists('request-scheme', $this->server)
            && strcasecmp($this->server['request-scheme'], 'https') === 0;
        return $https || $scheme;
    }

    /**
     * 创建URI
     *
     * @param RequestWrapper $request
     * @return void
     */
    private function createUri(RequestWrapper $request)
    {
        if (array_key_exists('document-root', $this->server)) {
            $index = (new IndexFinder(null, $this->server['document-root']))->getIndexFile();
        } else {
            $index = '';
        }
        $request->setIndex($index);
        $url = new UriParser($this->server['request-uri'] ?? '/', $index);
        $request->setQueries($url->getQuery());
        $request->setUri($url->getUri());
    }

    /**
     * 获取URI基础部分
     *
     * @param RequestWrapper $request
     * @return string
     */
    public static function createUriBase(RequestWrapper $request)
    {
        $scheme = $request->isSecure()?'https':'http';
        $port = $request->getPort();
        if ($port == 80 && $scheme == 'http') {
            $port = '';
        } elseif ($port == 433 && $scheme == 'https') {
            $port = '';
        } else {
            $port = ':'.$port;
        }
        $base = $scheme.'://'. $request->getHost().$port;
        return $base;
    }

    /**
     * 构建环境数据
     *
     * @param RawRequest $request
     * @return array
     */
    private function getFormatServer(RawRequest $request)
    {
        $server = [];
        foreach ($request->server() as $key => $value) {
            $name = strtolower(str_replace('_', '-', $key));
            $server[$name] = $value;
        }
        return $server;
    }
}
