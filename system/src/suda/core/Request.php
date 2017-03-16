<?php
namespace suda\core;

use suda\tool\Value;
use suda\tool\Json;

final class Request
{
    private static $get=null;
    private static $post=null;
    private static $files=null;
    private static $url;
    private static $request=null;
    private static $crawlers=[
            'TencentTraveler', 
            'Baiduspider+', 
            'BaiduGame', 
            'Googlebot', 
            'Baiduspider',
            'msnbot', 
            'Sosospider+', 
            'Sogou web spider', 
            'ia_archiver', 
            'Yahoo! Slurp', 
            'YoudaoBot', 
            'Yahoo Slurp', 
            'MSNBot', 
            'Java (Often spam bot)', 
            'BaiDuSpider', 
            'Voila', 
            'Yandex bot', 
            'BSpider', 
            'twiceler', 
            'Sogou Spider', 
            'Speedy Spider', 
            'Google AdSense', 
            'Heritrix', 
            'Python-urllib', 
            'Alexa (IA Archiver)', 
            'Ask', 
            'Exabot', 
            'Custo', 
            'OutfoxBot/YodaoBot', 
            'yacy', 
            'SurveyBot', 
            'legs', 
            'lwp-trivial', 
            'Nutch', 
            'StackRambler', 
            'The web archive (IA Archiver)', 
            'Perl tool', 
            'MJ12bot', 
            'Netcraft', 
            'MSIECrawler', 
            'WGet tools', 
            'larbin', 
            'Fish search',];
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
        return isset($_SERVER['REQUEST_METHOD'])?$_SERVER['REQUEST_METHOD']:'GET';
    }
    public static function url()
    {
        return self::$url;
    }
    
    public static function set(string $name, $value)
    {
        self::$get->$name=$value;
    }

    public static function get(string $name='')
    {
        if ($name) {
            return self::$get->$name;
        } else {
            return self::$get;
        }
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
            $ip = isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'127.0.0.1';
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
        return self::method()==='POST';
    }
    public static function hasGet()
    {
        return count($_GET);
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

    public static function getHeader(string $name,string $default=null){
        $name='HTTP_'.strtoupper(preg_replace('/[^\w]/','_',$name));
        if(isset($_SERVER[$name])){
            return $_SERVER[$name];
        }
        return $default;
    }

    protected static function parseServer()
    {
        $index=pathinfo(get_included_files()[0],PATHINFO_BASENAME);
        // 预处理
        //  /?/xxxxx
        //  /index.php/xxx
        //  /index.php?/xxx
        if (isset($_SERVER['REQUEST_URI'])) {
            if (preg_match('/^\/\?\//', $_SERVER['REQUEST_URI'])) {
                $preg='/^(\/\?(\/[^?]*))(?:[?](.+))?$/';
                preg_match($preg, $_SERVER['REQUEST_URI'], $match);
                $_SERVER['PHP_SELF']=$match[1];
                if (isset($match[3])) {
                    parse_str($match[3], $_GET);
                }
                self::$url=$match[2];
            } elseif (preg_match('/^(.*)\/'.$index.'(\??\/)?/', $_SERVER['REQUEST_URI'])) {
                $preg='/(.*)\/'.$index.'(\/[^?]*)?$/';
                preg_match($preg, $_SERVER['PHP_SELF'], $match);
                self::$url=isset($match[2])?$match[2]:'/';
            } else {
                $preg='/^([^?]*)/';
                preg_match($preg,$_SERVER['REQUEST_URI'],$match);
                self::$url=$match[1];
            }
            self::$url=self::$url==='/'?self::$url:rtrim(self::$url, '/');
        }
        else{
            self::$url='/';
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
    
    public static function baseUrl(){
        $scheme=isset($_SERVER['REQUEST_SCHEME'])?$_SERVER['REQUEST_SCHEME']:'//';
        $host=isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'';
        $base='';
        $script=$_SERVER['SCRIPT_NAME'];
        if ($host){
            $base=$scheme.'://'.$host;   
        }
        if (ltrim($script,'/')===conf('app.index','index.php')){
            return $base. (DIRECTORY_SEPARATOR ===  '/' ? '/':'/?/') ;
        }
        return $base. $script.'?/';
    }
    public function isCrawler(){
        $agent=isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'';
        foreach(self::$crawlers as $crawler){
            if( preg_match('/'.preg_quote($crawler).'/i',$agent)) {
                return $crawler;
            }
        }
        return false;
    }
}
