<?php
namespace suda\framework\request;

use suda\framework\Request;
use suda\framework\http\UploadedFile;
use suda\framework\request\IndexFinder;
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
     * @var \suda\framework\http\Request
     */
    protected $request;
    
    /**
     * 创建请求包装器
     *
     * @param \suda\framework\http\Request $request
     */
    public function __construct(RawRequest $request)
    {
        $this->request = $request;
    }

    /**
     * 构建对象
     *
     * @param Request $request
     * @return void
     */
    public function build(Request $request)
    {
        $request->setRemoteAddr($this->filterRemoteAddr());
        $request->setMethod(strtoupper($request->server['request-method'] ?? 'GET'));
        $request->setHost($this->getHttpHost());
        $request->setSecure($this->getSecure());
        $request->setPort($this->getServerPort());
        $this->createUri($request);
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
    private function createUri(Request $request)
    {
        if (\array_key_exists('document-root', $this->request->server)) {
            $index = (new IndexFinder(null, $this->request->server['document-root']))->getIndexFile();
        } else {
            $index = '';
        }
        $request->setIndex($index);
        $url = new UriParser($this->request->server['request-uri'] ?? '/', $index);
        $this->query = $url->getQuery();
        $request->setUri($url->getUri());
        $this->request->get = $url->getQuery();
    }
}
