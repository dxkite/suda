<?php
namespace suda\core;

use suda\tool\Json;
use suda\tool\ArrayHelper;
use suda\template\Manager;

// TODO: If-Modified-Since
// TODO: Access-Control


abstract class Response
{
    // 状态输出
    private static $status = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Moved Temporarily ',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        509 => 'Bandwidth Limit Exceeded',
    );
    private static $obstate=false;
    private $content='';
    private $type='html';
    private static $instance=null;
    private static $mime;
    protected static $name=null;
    protected static $_values=[];
    protected static $info=null;
    protected static $spaces;
    protected static $space_name='master';
    // 必须要调用
    /*final*/ public function __construct()
    {
        // Mark
        self::setHeader('X-Suda: '.conf('app.name', 'suda').'/'.conf('app.version').' ['.self::$name .']');
        if (conf('debug')) {
            // 设置无缓存头
            self::noCache();
            // 强制刷新index文件来避免缓存
            //@touch(get_included_files()[0]);
        }
        self::$instance=$this;
        self::$info['time']=microtime(true);
        self::$info['mem']=memory_get_usage();
        Debug::time('response');
    }
    
    abstract public function onRequest(Request $request);
    public function onPreTest($test_data)
    {
        return true;
    }
    public function onPreTestError($test_data)
    {
        echo 'onPreTestError';
        return true;
    }
    public static function state(int $state)
    {
        self::setHeader('HTTP/1.1 '.$state.' '.self::$status[$state]);
        self::setHeader('Status:'.$state.' '.self::$status[$state]);
    }


    public function type(string $type)
    {
        $this->type=$type;
        self::setHeader('Content-Type:'.self::mime($type));
    }

    public function noCache()
    {
        self::setHeader('Cache-Control: no-cache');
    }
    public function json($values)
    {
        _D()->I('Log Json:'.json_encode($values));
        self::mark();
        self::obEnd();
        if (is_array($values)) {
            $values=array_merge(self::$_values, $values);
        }
        $jsonstr=json_encode($values);
        if (Config::get('debug')) {
            $jsonstr.=$this->content;
        }
        self::type('json');
        Hook::exec('display:output', [&$jsonstr, $this->type]);
        self::setHeader('Content-Length:'.strlen($jsonstr));
        self::_etag(md5($jsonstr));
        echo $jsonstr;
    }

    public function file(string $path, string $type, int $size)
    {
        $hash=md5_file($path);
        $this->etag($hash);
        $this->type($type);
        self::setHeader('Content-Length:'.$size);
        echo file_get_contents($path);
    }

    public function display(string $template, array $values=[])
    {
        self::mark();
        // 结束缓冲控制
        self::obEnd();
        $values=array_merge(self::$_values, $values);
        // 渲染模板
        ob_start();
        self::assign($values);
        Manager::display($template);
        $this->content.=ob_get_clean();
        Hook::exec('display:output', [&$this->content, $this->type]);
        self::setHeader('Content-Length:'.strlen($this->content));
        self::_etag(md5($this->content));
        echo $this->content;
    }
    public function redirect(string $url, int $time=1)
    {
        $this->set('url', $url);
        $this->set('time', $time);
        $this->display('suda:redirect');
        $this->noCache();
    }

    public function displayFile(string $path, array $values=[])
    {
        self::mark();
        // 结束缓冲控制
        self::obEnd();
        // 渲染模板
        ob_start();
        self::assign($values);
        Manager::displayFile($path);
        $this->content.=ob_get_clean();
        Hook::exec('display:output', [&$this->content, $this->type]);
        self::setHeader('Content-Length:'.strlen($this->content));
        self::_etag(md5($this->content));
        echo $this->content;
    }
    public static function etag(string $etag)
    {
        self::setHeader('Etag:'.$etag);
        $request=Request::getInstance();
        if ($str=$request->getHeader('If-None-Match')) {
            if (strcasecmp($etag, $str)===0) {
                // _D()->d('Etag:'.$etag, 'Response 304');
                self::state(304);
                self::close();
                // 直接结束访问
                exit(0);
            }
        }
    }
    protected static function _etag(string $etag)
    {
        if (conf('app.etag', conf('debug'))) {
            self::etag($etag);
        }
    }
    protected static function mark()
    {
        Debug::timeEnd('response');
        $time=microtime(true) - self::$info['time'];
        $mem=memory_get_usage() - self::$info['mem'] ;
        self::setHeader('X-Suda: '.conf('app.name', 'suda').'/'.conf('app.version').' ['.self::$name .'] '."{$time}S {$mem}B");
    }
    public static function close()
    {
        self::setHeader('Connection: close');
    }
    public static function obStart()
    {
        if (!self::$obstate) {
            self::$obstate=true;
            ob_start();
        }
    }
    public static function setName(string $name)
    {
        self::$name=$name;
    }
    public static function space(string $name='master')
    {
        self::$spaces[self::$space_name]=self::$_values;
        self::$_values=[];
        self::$space_name=$name;
    }
    public static function reset(string $name='master')
    {
        self::$_values=self::$spaces[$name];
        self::$space_name=$name;
    }
    public static function set(string $name, $value)
    {
        return self::$_values=ArrayHelper::set(self::$_values, $name, $value);
    }

    public static function assign(array $values)
    {
        return self::$_values=array_merge(self::$_values, $values);
    }

    public static function get(string $name, $default=null)
    {
        $fmt= ArrayHelper::get(self::$_values, $name, $default ?? $name);
        if (func_num_args() > 2) {
            $args=array_slice(func_get_args(), 2);
            array_unshift($args, $fmt);
            return call_user_func_array('sprintf', $args);
        }
        return $fmt;
    }

    public function obEnd()
    {
        if (self::$obstate) {
            self::$obstate=false;
            $this->content.=ob_get_clean();
        }
    }

    public static function mime(string $name='')
    {
        if (!self::$mime) {
            self::$mime=parse_ini_file(SYS_RES.'/type.mime');
        }
        if ($name) {
            return self::$mime[$name] ?? 'text/plain';
        } else {
            return self::$mime;
        }
    }

    protected static function setHeader(string $header , bool $replace = true) {
        if (!headers_sent()){
            header($header,$replace);
        }
    }
}
