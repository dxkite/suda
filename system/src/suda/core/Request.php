<?php
/**
 * Suda FrameWork
 *
 * An open source application development framework for PHP 7.2.0 or newer
 *
 * Copyright (c)  2017-2018 DXkite
 *
 * @category   PHP FrameWork
 * @package    Suda
 * @copyright  Copyright (c) DXkite
 * @license    MIT
 * @link       https://github.com/DXkite/suda
 * @version    since 1.2.4
 */
namespace suda\core;

use suda\exception\JSONException;

/**
 * 请求描述类，客户端向框架发送请求时会生成此类
 */
class Request
{
    private static $json=null;

    private static $crawlers=null;
    protected static $instance=null;
    protected static $type=0;
    protected static $query='';
    protected static $url;
    protected static $baseUrl =null;
    protected static $host =null;
    protected static $port =null;
    protected static $scheme =null;
    protected static $script = null;

    protected $mapping=null;

    private function __construct()
    {
        // TODO parse command line to request
        self::parseRequest();
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance=new self;
        }
        return self::$instance;
    }

    public function setMapping($mapping)
    {
        $this->mapping=$mapping;
        return $this;
    }

    /**
     * 获取请求的JSON文档
     *
     * @return array|null 如果请求为json则数据是数组，否则数据为空
     */
    public static function json()
    {
        if (self::$json) {
            return self::$json;
        }
        if (!self::isJson() || self::isGet()) {
            return null;
        }
        $inputData=self::input();
        $data =json_decode($inputData, true);
        if (json_last_error()!==JSON_ERROR_NONE) {
            throw new JSONException(json_last_error());
        }
        return $data;
    }

    /**
     * 获取请求的原始输入
     *
     * @return string 读取请求的输入流内容
     */
    public static function input()
    {
        return file_get_contents('php://input');
    }

    /**
     * 获取请求的方法
     *
     * @return string 方法类型
     */
    public static function method() : string
    {
        return  strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * 获取请求的方法
     *
     * @return string 方法类型
     */
    public static function getMethod():string
    {
        return  self::method();
    }
    
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
     * 设置get的值
     *
     * @param string $name GET名
     * @param mixed $value GET的值
     * @return void
     */
    public static function set(string $name, $value)
    {
        $_GET[$name]=$value;
    }

    /**
     * 获取请求的GET数据
     *
     * @param string $name GET名
     * @param mixed $default GET值
     * @return mixed 获取的值
     */
    public static function get(?string $name=null, $default=null)
    {
        if (is_null($name)) {
            return $_GET;
        }
        if (array_key_exists($name, $_GET)) {
            if (\is_string($_GET[$name]) && strlen($_GET[$name])) {
                return $_GET[$name];
            } else {
                return $_GET[$name];
            }
        }
        return $default;
    }

    /**
     * 获取POST请求的值
     *
     * @param string $name
     * @param mixed $default
     * @return mixed 获取的值
     */
    public static function post(?string $name=null, $default=null)
    {
        if (is_null($name)) {
            return $_POST;
        }
        if (array_key_exists($name, $_POST)) {
            if (\is_string($_POST[$name]) && strlen($_POST[$name])) {
                return $_POST[$name];
            } else {
                return $_POST[$name];
            }
        }
        return $default;
    }

    /**
     * 获取请求的文件
     *
     * @param string $name 如果指定了文件则是所有的文件
     * @return array 文件属性
     */
    public static function files(?string $name=null)
    {
        if (is_null($name)) {
            return $_FILES;
        }
        if (array_key_exists($name, $_FILES)) {
            return $_FILES[$name];
        }
        return null;
    }

    /**
     * 获取Cookie的值
     *
     * @param string $name cookie名
     * @param mixed $default cookie的默认值
     * @return mixed 获取的值，如果没有，则是default设置的值
     */
    public static function cookie(string $name, $default ='')
    {
        return Cookie::get($name, $default);
    }

    /**
     * 获取请求的 IP
     *
     * @return string ip地址
     */
    public static function ip()
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
        return  '127.0.0.1';
    }

    /**
     * 判断是否是POST请求
     *
     * @return boolean
     */
    public static function isPost()
    {
        return self::method()==='POST';
    }

    /**
     * 判断是否是GET请求
     *
     * @return boolean
     */
    public static function isGet()
    {
        return self::method()==='GET';
    }


    
    /**
     * 判断是否有GET请求
     *
     * @param string|null $name
     * @return boolean
     */
    public static function hasGet(?string $name=null)
    {
        $get = self::get();
        if ($name) {
            return \array_key_exists($name, $get);
        }
        return count($get) > 0;
    }

    /**
     * 判断是否有POST数据请求
     *
     * @return boolean
     */
    public static function hasPost(?string $name=null)
    {
        $post = self::post();
        if ($name) {
            return \array_key_exists($name, $post);
        }
        return count($post) > 0;
    }

    /**
     * 判断是否有JSON数据请求
     *
     * @return boolean
     */
    public static function hasJson()
    {
        if (self::isJson()) {
            try {
                self::$json=self::json();
            } catch (\Exception $e) {
                return false;
            }
        }
        return true;
    }


    /**
     * 判断请求的数据是否为 json
     *
     * @return boolean
     */
    public static function isJson()
    {
        return array_key_exists('CONTENT_TYPE', $_SERVER) && preg_match('/json/i', $_SERVER['CONTENT_TYPE']);
    }
    
    /**
     * 根据IP生成HASH
     *
     * @return string ip地址的md5哈希值
     */
    public static function signature()
    {
        return md5(self::getHeader('User-Agent').self::ip());
    }

    /**
     * 获取请求头的内容
     *
     * @param string $name
     * @param string $default
     * @return string 请求头的内容
     */
    public static function getHeader(string $name, string $default=null):?string
    {
        $name='HTTP_'.strtoupper(preg_replace('/[^\w]/', '_', $name));
        if (array_key_exists($name, $_SERVER)) {
            return $_SERVER[$name];
        }
        return $default;
    }


    /**
     * 判断请求头中是否包含某一字段
     *
     * @param string $name 请求头名
     * @return boolean
     */
    public static function hasHeader(string $name):bool
    {
        $name='HTTP_'.strtoupper(preg_replace('/[^\w]/', '_', $name));
        return array_key_exists($name, $_SERVER);
    }


    /**
     * 处理请求的URL
     *
     * @param string $url
     * @return array ($path,$queryString,$phpSelf) 处理的数据
     */
    public static function parseUrl(string $url)
    {
        $path= '';
        $queryString='';
        $phpSelf='';
        
        $index=pathinfo(self::$script, PATHINFO_BASENAME);
        //  匹配 [1] /?/xxxxx
        if (preg_match('/^\/\?\//', $url)) {
            $preg='/^(\/\?(\/[^?]*))(?:[?](.+)?)?$/';
            preg_match($preg, $url, $match);
            $phpSelf=$match[1];
            $queryString=$match[3]??'';
            $path=$match[2];
        } elseif // 匹配 [2] /index.php?/
        // 匹配 [3] /index.php/xx
        (preg_match('/^(.*)\/'.$index.'(?:(\?)?\/)?/', $url, $check)) {
            // debug()->trace($check,$check[2]);
            $preg='/(.*)\/'.$index.'\??(\/[^?]*)?(?:[?](.+)?)?$/';
            self::$type=strlen($check[2]??'')>0?2:3;
            preg_match($preg, $url, $match);
            $queryString=$match[3]??'';
            $path= $match[2] ?? '/';
        } else {
            $preg='/^([^?]*)/';
            preg_match($preg, $url, $match);
            $path=$match[1];
        }
        $path=preg_replace('/[\/]+/', '/', $path);
        $path=($path==='/'?$path:rtrim($path, '/'));
        return [$path,$queryString,$phpSelf];
    }

    protected static function parseRequest()
    {
        // 获取主入口文件
        self::$script = str_replace('\\', '/', IN_PHAR?substr(SUDA_ENTRANCE, strlen('phar://'.$_SERVER['DOCUMENT_ROOT'])):substr(SUDA_ENTRANCE, strlen($_SERVER['DOCUMENT_ROOT'])));
        list(self::$url, $queryString, $phpSelf) = static::parseUrl($_SERVER['REQUEST_URI']??'/');
        $_SERVER['PATH_INFO'] = self::$url;
        $_SERVER['SCRIPT_NAME'] = self::$script;

        if ($queryString && strlen($queryString)) {
            parse_str($queryString, $queryGET);
            $_GET = array_merge($_GET, $queryGET);
        }

        if ($phpSelf) {
            $_SERVER['PHP_SELF'] = $phpSelf;
        }
        
        if (isset($_GET[self::$url])) {
            unset($_GET[self::$url]);
        }
    }

    public static function virtualUrl()
    {
        return self::$url.(self::$query?'?'.self::$query:'');
    }

    public static function referer()
    {
        return $_SERVER['HTTP_REFERER']??null;
    }

    public static function hostBase()
    {
        $scheme = self::getScheme();
        $host= self::getHost();
        return $scheme.'://'.$host;
    }

    public static function getScheme()
    {
        if (is_null(self::$scheme)) {
            if (array_key_exists('HTTPS', $_SERVER) && strcasecmp($_SERVER['HTTPS'], 'off') != 0) {
                self::$scheme = 'https';
            } elseif (array_key_exists('REQUEST_SCHEME', $_SERVER)) {
                self::$scheme = conf('app.router.scheme', $_SERVER['REQUEST_SCHEME']);
            } else {
                self::$scheme = 'http';
            }
        }
        return self::$scheme;
    }
    
    public static function getHost()
    {
        if (is_null(self::$host)) {
            self::$host = conf('app.router.host', $_SERVER['HTTP_HOST'] ?? 'localhost');
        }
        return self::$host;
    }

    public static function getPort()
    {
        if (is_null(self::$port)) {
            self::$port = conf('app.router.port', $_SERVER["SERVER_PORT"] ?? 80);
        }
        return self::$port;
    }

    public static function baseUrl()
    {
        if (is_null(self::$baseUrl)) {
            self::$baseUrl = self::getBaseUrl();
        }
        return self::$baseUrl;
    }

    protected static function getBaseUrl():string
    {
        // 0 auto
        // 1 windows = /?/, linux = /
        // 2 index.php/
        // 3 index.php?/
        $base=self::hostBase();
        $script= self::$script;
        $mode=conf('app.url.mode', 0);
        $beautify=conf('app.url.beautify', false);
        $index=conf('app.index', 'index.php');
        if ($mode==0 || $mode==1) {
            // 如果当前脚本为AutoIndex索引
            if (ltrim($script, '/\\') === $index) {
                // 开启重写
                if (conf('app.url.rewrite', false)) {
                    return $base. ((IS_LINUX || $beautify)? '/':'/?/');
                }
                return $base.'/?/';
            }
        } elseif ($mode==2) {
            return $base.$script.'/';
        } elseif ($mode==3) {
            return $base.$script.'?/';
        }
        return $base.$script.(self::$type==2?'?':'').'/';
    }
    
    public function isCrawler()
    {
        $agent= $_SERVER['HTTP_USER_AGENT'] ?? '';
        if (is_null(self::$crawlers)) {
            self::$crawlers=require SYSTEM_RESOURCE.'/crawlers.php';
        }
        foreach (self::$crawlers as $crawler) {
            if (preg_match('/'.preg_quote($crawler).'/i', $agent)) {
                return $crawler;
            }
        }
        return false;
    }

    public function getMapping()
    {
        return $this->mapping;
    }
}
