<?php
namespace suda\core\request;

/**
 * 请求处理类
 * 按照框架的模式处理请求
 */
trait RequestParser
{
    protected static $query = '';
    protected static $url;
    protected static $baseUrl = null;
    protected static $script = null;

    /**
     * 获取请求的URL数据
     *
     * @return string 处理过的URL
     */
    public static function url():string
    {
        return self::$url;
    }
    
    /**
     * 处理请求的URL
     *
     * @param string $url
     * @return array 处理的数据 格式：array($path,$queryString,$phpSelf)
     */
    public static function parseUrl(string $url)
    {
        $queryString = '';
        // for /?/xx
        if (\strpos($url, '/?/') === 0) {
            $url = substr($url, 2);
        }
        $phpSelf = $indexFile = self::$script;
        if (\strpos($url, $indexFile) === 0) {
            // for /index.php/
            $url = \substr($url, strlen($indexFile));// for /index.php?/
            if (\strpos($url, '?/') === 0) {
                $url = ltrim($url, '?');
            }
            // for /index.php
            elseif (\strpos($url, '/') !== 0) {
                $url = '/'.$url;
            }
        }
        $queryStart = \strpos($url, '?');
        if ($queryStart !== false) {
            $queryString = \substr($url, $queryStart + 1);
            $url = \substr($url, 0, $queryStart);
        }
        return [$url,$queryString,$phpSelf];
    }

    protected static function parseRequest()
    {
        // 获取主入口文件
        self::$script = str_replace('\\', '/', IN_PHAR?substr(SUDA_ENTRANCE, strlen('phar://'.$_SERVER['DOCUMENT_ROOT'])):substr(SUDA_ENTRANCE, strlen($_SERVER['DOCUMENT_ROOT'])));
        list(self::$url, $queryString, $phpSelf) = static::parseUrl($_SERVER['REQUEST_URI'] ?? '/');
        $_SERVER['PATH_INFO'] = self::$url;
        $_SERVER['SCRIPT_NAME'] = self::$script;
        $_GET = [];
        if (!empty($queryString) && strlen($queryString) > 0) {
            parse_str($queryString, $queryGET);
            $_GET = $queryGET;
        }
        if (!empty($phpSelf)) {
            $_SERVER['PHP_SELF'] = $phpSelf;
        }
    }

    public static function virtualUrl()
    {
        return self::$url.(self::$query?'?'.self::$query:'');
    }

    public static function baseUrl(bool $withHosts = false)
    {
        if (null === self::$baseUrl) {
            self::$baseUrl = self::getBaseUrl($withHosts);
        }
        return self::$baseUrl;
    }

    protected static function getBaseUrl(bool $withHosts = false):string
    {
        $index = conf('app.index', 'index.php');
        $base = $withHosts? self::hostBase() :'';
        $script = self::$script;
        // $mode=conf('app.url.mode', 0);
        $beautifyUrl = conf('app.url.beautify', true);
        $rewrite = conf('app.url.rewrite', false);
        $root = substr($script, 1) === $index;
        $isWindows = !IS_LINUX;
        // 如果开启了重写URL
        if ($rewrite && $root) {
            if ($isWindows && !$beautifyUrl) {
                return $base.'/?/';
            }
            return $base.'/';
        }
        return $base.$script.'/';
    }
}
