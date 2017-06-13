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
    
    private static $mime;
    public static $name;

    public function __construct()
    {
        // Mark
        self::setHeader('X-Suda : Suda/'.SUDA_VERSION.' '.conf('app.name', 'suda-app').'/'.conf('app.version').' '.self::$name);
        if (conf('debug')) {
            self::noCache();
            // for windows debug touch file to avoid 304 by server
            if (DIRECTORY_SEPARATOR==='\\') {
                $script=$_SERVER['SCRIPT_NAME'];
                $file=APP_PUBLIC.$script;
                $content=file_get_contents($file);
                if (preg_match('/\<\?php\s+#\d+\r\n/i', $content)) {
                    $content=preg_replace('/\<\?php\s+#\d+\r\n/i', '<?php #'.time()."\r\n", $content);
                } else {
                    $content=preg_replace('/\<\?php/i', '<?php #'.time()."\r\n", $content);
                }
                file_put_contents($file, $content);
            }
        }
    }
    
    abstract public function onRequest(Request $request);
    
    public static function state(int $state)
    {
        self::setHeader('HTTP/1.1 '.$state.' '.self::$status[$state]);
        self::setHeader('Status:'.$state.' '.self::$status[$state]);
    }
    public static function setName(string $name)
    {
        self::$name=$name;
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

    /**
    * 构建JSON输出
    */
    public function json($values)
    {
        $jsonstr=json_encode($values);
        self::type('json');
        Hook::exec('display:output', [&$jsonstr, $this->type]);
        self::setHeader('Content-Length:'.strlen($jsonstr));
        self::_etag(md5($jsonstr));
        echo $jsonstr;
    }

    /**
    *  直接输出文件
    */
    public function file(string $path, string $type=null)
    {
        $content=file_get_contents($path);
        $hash   = md5($content);
        $size   = strlen($content);
        $this->_etag($hash);
        $type   = $type || pathinfo($path, PATHINFO_EXTENSION);
        $this->type($type);
        self::setHeader('Content-Length:'.$size);
        echo $content;
    }

    /**
    * 输出HTML页面
    * $template HTML页面模板
    * $values 页面模板的值
    */
    public function page(string $template, array $values=[])
    {
        return Manager::display($template)->response($this)->assign($values);
    }
        /**
    * 输出HTML页面
    * $template HTML页面模板
    * $values 页面模板的值
    */
    public function pagefile(string $template, string $name, array $values=[])
    {
        return Manager::displayFile($template, $name)->response($this)->assign($values);
    }

    public function refresh()
    {
        $this->go(u(self::$name));
    }
    public function go(string $url)
    {
        $this->setHeader('Location:'.$url);
    }
    public function redirect(string $url, int $time=1, string $message=null)
    {
        $this->noCache();
        $page=$this->page('suda:redirect');
        if ($message) {
            $page->set('message', $message);
        }
        $page->set('url', $url);
        $page->set('time', $time);
        $page->render();
    }

    public static function etag(string $etag)
    {
        self::setHeader('Etag:'.$etag);
        $request=Request::getInstance();
        if ($str=$request->getHeader('If-None-Match')) {
            if (strcasecmp($etag, $str)===0) {
                self::state(304);
                self::close();
                // 直接结束访问
                exit(0);
            }
        }
    }

    protected static function _etag(string $etag)
    {
        if (conf('app.etag', !conf('debug'))) {
            self::etag($etag);
        }
    }
    
    public static function close()
    {
        self::setHeader('Connection: close');
    }

    /**
    *  页面MIME类型
    */
    public static function mime(string $name='')
    {
        if (!self::$mime) {
            self::$mime=parse_ini_file(SYSTEM_RESOURCE.'/type.mime');
        }
        if ($name) {
            return self::$mime[$name] ?? 'text/plain';
        } else {
            return self::$mime;
        }
    }

    /**
    * 安全设置Header值
    */
    public static function setHeader(string $header, bool $replace = true)
    {
        if (!headers_sent()) {
            header($header, $replace);
        }
    }
}
