<?php
namespace suda\core;

use suda\tool\Json;
use suda\template\Manager;

class Response
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
    public function __construct()
    {
        Header('X-Framework: DxCore-Suda');
    }

    public function onRequest(Request $request)
    {
    }
    public function onPreTest($test_data):bool
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
        header('HTTP/1.1 '.$state.' '.self::$status[$state]);
        header('Status:'.$state.' '.self::$status[$state]);
    }


    public function type(string $type)
    {
        $this->type=$type;
        header('Content-Type:'.self::mime($type));
    }


    public function json($values)
    {
        self::obEnd();
        $jsonstr=json_encode($values);
        if (Config::get('debug')) {
            $jsonstr.=$this->content;
        }
        self::type('json');
        Hook::exec('display:output', [&$jsonstr, $this->type]);
        Header('Content-Length:'.strlen($jsonstr));
        echo $jsonstr;
    }

    public function display(string $template, array $values=[])
    {
        // 结束缓冲控制
        self::obEnd();
        // 渲染模板
        ob_start();
        Manager::display($template, $values);
        $this->content.=ob_get_clean();
        Hook::exec('display:output', [&$this->content, $this->type]);
        Header('Content-Length:'.strlen($this->content));
        echo $this->content;
    }

    public function displayFile(string $path,array $values=[]){
        // 结束缓冲控制
        self::obEnd();
        // 渲染模板
        ob_start();
        Manager::displayFile($path, $values);
        $this->content.=ob_get_clean();
        Hook::exec('display:output', [&$this->content, $this->type]);
        Header('Content-Length:'.strlen($this->content));
        echo $this->content;
    }

    public static function obStart()
    {
        if (!self::$obstate) {
            self::$obstate=true;
            ob_start();
        }
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
            self::$mime=parse_ini_file(DATA_DIR.'/type.mime');
        }
        if ($name) {
            return isset(self::$mime[$name])?self::$mime[$name]:'text/plain';
        } else {
            return self::$mime;
        }
    }
}
