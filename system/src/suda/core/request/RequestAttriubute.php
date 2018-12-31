<?php
namespace suda\core\request;

/**
 * 请求描述类，客户端向框架发送请求时会生成此类
 */
trait RequestAttriubute
{
    protected static $host =null;
    protected static $port =null;
    protected static $scheme =null;
    protected $mapping=null;

    public function setMapping($mapping)
    {
        $this->mapping=$mapping;
        return $this;
    }

     
    public function getMapping()
    {
        return $this->mapping;
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

    public static function getRequestUrl():string
    {
        return $_SERVER['REQUEST_URI'] ?? '/';
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
     * 根据IP生成HASH
     *
     * @return string ip地址的md5哈希值
     */
    public static function signature()
    {
        return md5(self::getHeader('User-Agent').self::ip());
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
}
