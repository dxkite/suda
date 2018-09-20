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

use suda\tool\Value;
use suda\exception\JSONException;

/**
 * 请求描述类，客户端向框架发送请求时会生成此类
 */
class Request
{
    private static $get=null;
    private static $post=null;
    private static $json=null;
    private static $files=null;
    private static $url;
    private static $type=0;
    private static $instance=null;
    private static $query='';
    private static $crawlers=null;
    private $mapping=null;

    private function __construct()
    {
        if (!isset($_SERVER['REQUEST_URI'])) {
            self::parseCommand();
        }
        self::parseServer();
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
        $datastr=self::input();
        $data =json_decode($datastr, true);
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
     * @param [type] $value GET的值
     * @return void
     */
    public static function set(string $name, $value)
    {
        self::$get->$name=$value;
    }


    /**
     * 获取请求的GET数据
     *
     * @param string $name GET名
     * @param [type] $default GET值
     * @return [type] 获取的值
     */
    public static function get(string $name='', $default=null)
    {
        if ($name) {
            if (isset(self::$get->$name)) {
                return self::$get->$name;
            } else {
                return $default;
            }
        } else {
            return self::$get;
        }
    }
    
    /**
     * 获取Cookie的值
     *
     * @param string $name cookie名
     * @param [type] $default cookie的默认值
     * @return [type] 获取的值，如果没有，则是default设置的值
     */
    public static function cookie(string $name, $default ='')
    {
        return Cookie::get($name, $default);
    }


    /**
     * 获取POST请求的值
     *
     * @param string $name
     * @param [type] $default
     * @return [type] 获取的值
     */
    public static function post(string $name='', $default=null)
    {
        if ($name) {
            if (isset(self::$post->$name)) {
                return self::$post->$name;
            } else {
                return $default;
            }
        } else {
            return self::$post;
        }
    }

    /**
     * 获取请求的文件
     *
     * @param string $name 如果指定了文件则是所有的文件
     * @return array 文件属性
     */
    public static function files(string $name='')
    {
        if ($name) {
            return self::$files->$name;
        } else {
            return self::$files;
        }
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
                    if ((bool) filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 |FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
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
     * @return boolean
     */
    public static function hasGet()
    {
        return count($_GET);
    }

    /**
     * 判断是否有POST数据请求
     *
     * @return boolean
     */
    public static function hasPost()
    {
        return count($_POST);
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
        return isset($_SERVER['CONTENT_TYPE']) && preg_match('/json/i', $_SERVER['CONTENT_TYPE']);
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
        if (isset($_SERVER[$name])) {
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
        return isset($_SERVER[$name]);
    }


    /**
     * 处理请求的URL
     *
     * @param string $url
     * @return array($path,$queryString,$phpSelf) 处理的数据
     */
    public static function parseUrl(string $url)
    {
        $path= '';
        $queryString='';
        $phpSelf='';
        $index=pathinfo(get_included_files()[0], PATHINFO_BASENAME);
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
            self::$type=strlen($check[2]??false)>0?2:3;
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

    // TODO:for shell
    protected static function parseCommand()
    {
        $command=getopt('r:');
        if (isset($command['r'])) {
            $_SERVER['REQUEST_URI']=$command['r'];
        }
    }

    protected static function parseServer()
    {
        list(self::$url, $queryString, $phpSelf) = static::parseUrl($_SERVER['REQUEST_URI']??'/');

        if ($queryString) {
            if (isset($queryString)) {
                // 重定义GET
                $_GET=[];
                parse_str($queryString, $_GET);
            }
        }

        if ($phpSelf) {
            $_SERVER['PHP_SELF'] = $phpSelf;
        }
        
        if (isset($_GET[self::$url])) {
            unset($_GET[self::$url]);
        }

        if (!isset($_SERVER['PATH_INFO'])) {
            $_SERVER['PATH_INFO']=self::$url;
        }
       
        if (is_null(self::$post)) {
            self::$post=new Value($_POST);
        }
        if (is_null(self::$get)) {
            self::$get=new Value($_GET);
        }
        if (is_null(self::$files)) {
            self::$files=new Value($_FILES);
        }
    }

    public static function virtualUrl()
    {
        return self::$url.(self::$query?'?'.self::$query:'');
    }

    public static function referer()
    {
        return $_SERVER['HTTP_REFERER']??false;
    }

    public static function hostBase()
    {
        $scheme = self::getScheme();
        $host= self::getHost();
        // $_SERVER['HTTP_HOST'] 包含端口
        return $scheme.'://'.$host;
    }

    public static function getScheme()
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']!='off') {
            return 'https';
        }
        if (isset($_SERVER['REQUEST_SCHEME'])) {
            return conf('app.router.scheme', $_SERVER['REQUEST_SCHEME']);
        }
        return 'http';
    }
    
    public static function getHost()
    {
        return  conf('app.router.host', $_SERVER['HTTP_HOST'] ?? 'localhost');
    }

    public static function getPort()
    {
        return conf('app.router.port', $_SERVER["SERVER_PORT"]??80);
    }

    public static function baseUrl()
    {
        // 0 auto
        // 1 windows = /?/, linux = /
        // 2 index.php/
        // 3 index.php?/
        $base=self::hostBase();
        $script=$_SERVER['SCRIPT_NAME'];
        $module=conf('app.url.mode', 0);
        $beautify=conf('app.url.beautify', false);
        if ($module==0 || $module==1) {
            // 如果当前脚本为AutoIndex索引
            if (ltrim($script, '/')===conf('app.index', 'index.php')) {
                // 开启重写
                if (conf('app.url.rewrite', false)) {
                    return $base. ((IS_LINUX || $beautify)? '/':'/?/');
                }
                return $base.'/?/';
            }
        } elseif ($module==2) {
            return $base.$script.'/';
        } elseif ($module==3) {
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
