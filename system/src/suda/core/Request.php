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
use suda\core\request\RequestAttriubute;
/**
 * 请求描述类，客户端向框架发送请求时会生成此类
 */
class Request 
{
    use RequestAttriubute;
    private static $json=null;
    protected static $instance=null;
    protected static $type=0;
    protected static $query='';
    protected static $url;
    protected static $baseUrl =null;
    protected static $script = null;

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
     * 处理请求的URL
     *
     * @param string $url
     * @return array 处理的数据 格式：array($path,$queryString,$phpSelf)
     */
    public static function parseUrl(string $url)
    {
        $queryString='';
        // for /?/xx
        if (\strpos($url, '/?/') === 0) {
            $url = substr($url, 2);
        }
        $phpSelf= $indexFile = self::$script;
        if (\strpos($url, $indexFile) ===0) {
            // for /index.php/
            $url = \substr($url, strlen($indexFile));// for /index.php?/
            if (\strpos($url, '?/') === 0) {
                $url = ltrim($url, '?');
            }
            // for /index.php
            elseif (\strpos($url, '/')!== 0) {
                $url = '/'.$url;
            }
        }
        $queryStart = \strpos($url, '?');
        if ($queryStart !== false) {
            $queryString = \substr($url, $queryStart+1);
            $url = \substr($url, 0, $queryStart);
        }
        return [$url,$queryString,$phpSelf];
    }

    protected static function parseRequest()
    {
        // 获取主入口文件
        self::$script = str_replace('\\', '/', IN_PHAR?substr(SUDA_ENTRANCE, strlen('phar://'.$_SERVER['DOCUMENT_ROOT'])):substr(SUDA_ENTRANCE, strlen($_SERVER['DOCUMENT_ROOT'])));
        list(self::$url, $queryString, $phpSelf) = static::parseUrl($_SERVER['REQUEST_URI']??'/');
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

    public static function baseUrl()
    {
        if (is_null(self::$baseUrl)) {
            self::$baseUrl = self::getBaseUrl();
        }
        return self::$baseUrl;
    }

    protected static function getBaseUrl():string
    {
        $index=conf('app.index', 'index.php');
        $base=self::hostBase();
        $script= self::$script;
        // $mode=conf('app.url.mode', 0);
        $beautifyUrl=conf('app.url.beautify', true);
        $rewrite=conf('app.url.rewrite', false);
        $root= substr($script, 1) === $index;
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
