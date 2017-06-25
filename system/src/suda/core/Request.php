<?php
/**
 * Suda FrameWork
 *
 * An open source application development framework for PHP 7.0.0 or newer
 * 
 * Copyright (c)  2017 DXkite
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
use suda\tool\Json;

final class Request
{
    private static $get=null;
    private static $post=null;
    private static $json=null;
    private static $files=null;
    private static $url;
    private static $type=0;
    private static $request=null;
    private static $query='';
    private static $crawlers=null;
    private function __construct()
    {
        if (!isset($_SERVER['REQUEST_URI'])) {
            self::parseCommand();
        }
        self::parseServer();
    }

    public static function getInstance()
    {
        if (is_null(self::$request)) {
            self::$request=new Request();
        }
        return self::$request;
    }

    public static function json()
    {
        if(self::$json) return self::$json;
        if (!self::isJson()) {
            return null;
        }
        $str=self::input();
        return Json::decode($str, true);
    }

    public static function input()
    {
        return file_get_contents('php://input');
    }
    
    public static function method()
    {
        return  $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    public static function getMethod()
    {
        return  self::method();
    }
    
    public static function url()
    {
        return self::$url;
    }
    
    public static function set(string $name, $value)
    {
        self::$get->$name=$value;
    }

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
    public static function cookie(string $name, $default)
    {
        return Cookie::get($name, $default);
    }
    public static function post(string $name='')
    {
        if ($name) {
            return self::$post->$name;
        } else {
            return self::$post;
        }
    }
    public static function files(string $name='')
    {
        if ($name) {
            return self::$files->$name;
        } else {
            return self::$files;
        }
    }

    public static function ip()
    {
        if (getenv('HTTP_CLIENT_IP')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('HTTP_X_FORWARDED')) {
            $ip = getenv('HTTP_X_FORWARDED');
        } elseif (getenv('HTTP_FORWARDED_FOR')) {
            $ip = getenv('HTTP_FORWARDED_FOR');
        } elseif (getenv('HTTP_FORWARDED')) {
            $ip = getenv('HTTP_FORWARDED');
        } else {
            $ip =  $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        }
        return $ip;
    }

    public static function ip2Address($ip)
    {
        $url="http://ip.taobao.com/service/getIpInfo.php?ip=".$ip;
        $ip=json_decode(@file_get_contents($url), true);
        return $ip;
    }

    public static function isPost()
    {
        return strtoupper(self::method())==='POST';
    }

    public static function isGet()
    {
        return strtoupper(self::method())==='GET';
    }

    public static function hasGet()
    {
        return count($_GET);
    }

    public static function hasPost()
    {
        return count($_POST);
    }

    public static function hasJson()
    {
        if(self::isJson()){
            try{
                self::$json=self::json();
            }catch(\Exception $e){
                return false;
            }
        }
        return true;
    }

    public static function isJson()
    {
        return isset($_SERVER['CONTENT_TYPE']) && preg_match('/json/i', $_SERVER['CONTENT_TYPE']);
    }
    
    public static function signature()
    {
        return md5(self::ip());
    }

    // TODO:for shell
    protected static function parseCommand()
    {
        $command=getopt('r:');
        if (isset($command['r'])) {
            $_SERVER['REQUEST_URI']=$command['r'];
        }
    }

    public static function getHeader(string $name, string $default=null)
    {
        $name='HTTP_'.strtoupper(preg_replace('/[^\w]/', '_', $name));
        if (isset($_SERVER[$name])) {
            return $_SERVER[$name];
        }
        return $default;
    }
    public static function hasHeader(string $name, string $default=null)
    {
        $name='HTTP_'.strtoupper(preg_replace('/[^\w]/', '_', $name));
        return isset($_SERVER[$name]);
    }
    protected static function parseServer()
    {
        $index=pathinfo(get_included_files()[0], PATHINFO_BASENAME);
        // 预处理 请求
        if (isset($_SERVER['REQUEST_URI'])) {
            //  匹配 [1] /?/xxxxx
            if (preg_match('/^\/\?\//', $_SERVER['REQUEST_URI'])) {
                $preg='/^(\/\?(\/[^?]*))(?:[?](.+)?)?$/';
                preg_match($preg, $_SERVER['REQUEST_URI'], $match);
                $_SERVER['PHP_SELF']=$match[1];
                // 处理查询字符
                if (isset($match[3])) {
                    $_GET=array();
                    parse_str($match[3], $_GET);
                }
                self::$query=$match[3]??'';
                self::$url=$match[2];
                self::$type=1;
            } elseif // 匹配 [2] /index.php?/
            // 匹配 [3] /index.php/xx
            (preg_match('/^(.*)\/'.$index.'(?:(\?)?\/)?/', $_SERVER['REQUEST_URI'], $check)) {
                // _D()->trace($check,$check[2]);
                $preg='/(.*)\/'.$index.'\??(\/[^?]*)?(?:[?](.+)?)?$/';
                self::$type=strlen($check[2]??false)>0?2:3;
                preg_match($preg, $_SERVER['REQUEST_URI'], $match);
                // _D()->trace($preg,$_SERVER['REQUEST_URI'].' '. serialize($match));
                // 处理查询字符
                if (isset($match[3])) {
                    $_GET=array();
                    parse_str($match[3], $_GET);
                }
                self::$query=$match[3]??'';
                self::$url= $match[2] ?? '/';
            } else {
                // 匹配 [0] /
                self::$type=0;
                $preg='/^([^?]*)/';
                preg_match($preg, $_SERVER['REQUEST_URI'], $match);
                self::$url=$match[1];
            }
            self::$url=preg_replace('/[\/]+/','/',self::$url);
            self::$url=self::$url==='/'?self::$url:rtrim(self::$url, '/');
        } else {
            self::$url='/';
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

    public static function hostBase(){
        $scheme = $_SERVER['REQUEST_SCHEME'] ?? '//';
        $host= $_SERVER['HTTP_HOST'] ?? '';
        return $scheme.'://'.$host;
    }

    public static function baseUrl()
    {
        $base=self::hostBase();
        $script=$_SERVER['SCRIPT_NAME'];   
        if (ltrim($script, '/')===conf('app.index', 'index.php')) {
            // windows下rewrite重写会出现各种奇怪的异常
            if(conf('app.rewrite',false)){
                return $base. (DIRECTORY_SEPARATOR ===  '/' ? '/':'/?/');
            }
            return $base.'/';
        }
        // _D()->trace(self::$type);
        return $base. $script.(self::$type==2?'?':'').'/';
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
}
