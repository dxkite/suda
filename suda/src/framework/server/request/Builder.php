<?php
namespace suda\framework\server\request;

use suda\framework\server\Request;
use suda\framework\server\request\UriParser;
use suda\framework\server\request\IndexFinder;
use suda\framework\server\request\UploadedFile;

/**
 * HTTP 入口解析查找
 */
class Builder
{
    public static function create():Request
    {
        $builder = new static;
        return $builder->wrapperRequest();
    }
    
    /**
     * 从服务器载入数据
     *
     * @return Request
     */
    private function wrapperRequest(): Request
    {
        $request = new Request;
        $request->setRemoteAddr($this->filterRemoteAddr());
        $request->setMethod(strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        $request->setHost($this->getHttpHost());
        $request->setSecure($this->getSecure());
        $request->setPort($this->getServerPort());
        $request->setHeaders($this->createHeaders());
        $this->createFiles($request);
        $this->createUri($request);
        return $request;
    }

    /**
     * 获取IP地址
     *
     * @return string
     */
    private function filterRemoteAddr():string
    {
        static $ipFrom = ['HTTP_CLIENT_IP','HTTP_X_FORWARDED_FOR','HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP','HTTP_FORWARDED_FOR','HTTP_FORWARDED','REMOTE_ADDR'];
        foreach ($ipFrom as $key) {
            if (array_key_exists($key, $_SERVER)) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
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
        if (array_key_exists('HTTP_HOST', $_SERVER)) {
            return explode(':', $_SERVER['HTTP_HOST'])[0];
        }
        return 'localhost';
    }

    /**
     * 获取端口
     *
     * @return integer
     */
    private function getServerPort():int
    {
        if (array_key_exists('SERVER_PORT', $_SERVER)) {
            return $_SERVER['SERVER_PORT'];
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
        $https = array_key_exists('HTTPS', $_SERVER) && strcasecmp($_SERVER['HTTPS'], 'off') != 0;
        $scheme = array_key_exists('REQUEST_SCHEME', $_SERVER) && strcasecmp($_SERVER['REQUEST_SCHEME'], 'https') === 0;
        return $https || $scheme;
    }

    /**
     * 创建请求文件
     *
     * @return void
     */
    private function createFiles(Request $request)
    {
        foreach ($_FILES as $name => $file) {
            $this->files[$name] = new UploadedFile($file['tmp_name'], $file['name'], $file['type'], $file['error']);
        }
    }

    private function createUri(Request $request)
    {
        $index = (new IndexFinder)->getIndexFile();
        $request->setIndex($index);
        $url = new UriParser($_SERVER['REQUEST_URI'] ?? '/', $index);
        foreach ($url->getQuery() as $key => $value) {
            $request->setQuery($key, $value);
        }
        $request->setUri($url->getUri());
        $_GET = $url->getQuery();
    }

    private function createHeaders()
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $name = substr($key, strlen('HTTP_'));
                $name = \strtolower(\str_replace('_', '-', $name));
                $headers[$name] = $value;
            }
        }
        return $headers;
    }
}
