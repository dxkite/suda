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

use suda\tool\ArrayHelper;
use suda\template\Manager;
use suda\exception\ApplicationException;
use suda\exception\JSONException;

// TODO: If-Modified-Since
// TODO: Access-Control

/**
 * 网页响应类，用于处理来自服务器的请求
 * 
 */
abstract class Response
{
    // 状态输出
    private static $status =null;
    private static $mime;
    public static $name;
    protected $type;
    const EnableOutputBuffer = true;

    public function __construct()
    {
        // Mark Version
        if (conf('markVersion', true)) {
            self::setHeader('X-Application:'.conf('app.name', 'suda-app').'/'.conf('app.version').' '.self::$name .' request/' .conf('request'));
        }
        
        if (conf('debug')) {
            self::noCache();
            // for windows debug touch file to avoid 304 by server
            if (!IS_LINUX) {
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
        self::setHeader('HTTP/1.1 '.$state.' '.self::statusMessage($state));
        self::setHeader('Status:'.$state.' '.self::statusMessage($state));
    }

    public static function setName(string $name)
    {
        self::$name=$name;
    }

    public static function getName()
    {
        return self::$name;
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
        $jsonstr=json_encode($values, JSON_UNESCAPED_UNICODE);
        if ($jsonstr === false && json_last_error()!==JSON_ERROR_NONE) {
            throw new JSONException(json_last_error());
        }
        self::type('json');
        Hook::exec('display:output', [&$jsonstr, $this->type]);
        if (conf('app.calcContentLength', !conf('debug'))) {
            self::setHeader('Content-Length:'.strlen($jsonstr));
        }
        self::_etag(md5($jsonstr));
        echo $jsonstr;
    }

    /**
    *  直接输出文件
    */
    public function file(string $path, string $filename=null, string $type=null)
    {
        $content=file_get_contents($path);
        $hash   = md5($content);
        $size   = strlen($content);
        if (!$this->_etag($hash)) {
            $type   = $type ?? pathinfo($path, PATHINFO_EXTENSION);
            $this->type($type);
            self::setHeader('Content-Disposition: attachment;filename="'.$filename.'.'.$type.'"');
            self::setHeader('Content-Length:'.$size);
            self::setHeader('Cache-Control: max-age=0');
            self::setHeader('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            self::setHeader('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
            self::setHeader('Cache-Control: cache, must-revalidate'); // HTTP/1.1
            self::setHeader('Pragma: public'); // HTTP/1.0
            echo $content;
        }
    }

    /**
    * 输出HTML页面
    * $template HTML页面模板
    * $values 页面模板的值
    */
    public function page(string $template, array $values=[])
    {
        $view = $this->view($template, $values);
        if ($view) {
            return $view;
        }
        throw new ApplicationException(__('template[%s] file not exist: %s', $template, $template));
    }

    /**
    * 输出HTML页面
    * $template HTML页面模板
    * $values 页面模板的值
    */
    public function view(string $template, array $values=[])
    {
        $tpl=Manager::displaySource($template,'html');
        if ($tpl) {
            return $tpl->response($this)->assign($values);
        }
        return false;
    }

    /**
     * 输出模板
     *
     * @param string $template 模板路径
     * @param array $values 页面值
     * @param string $name 模板名
     * @return void
     */
    public function template(string $filepath, array $values=[], ?string  $name=null)
    {
        // Template lost
        $tpl=Manager::displayFile($filepath, $name);
        if ($tpl) {
            return $tpl->response($this)->assign($values);
        }
        return false;
    }

    public function refresh()
    {
        return $this->go(Router::getInstance()->buildUrl(self::$name, $_GET, false));
    }

    public function forward():bool {
        if ($forward = self::getForward()) {
            $this->go($forward);
            return true;
        }
        return false;
    }

    public function getForward():?string
    {
        $referer =  $_GET['redirect_uri']??Request::referer();
        if (Cookie::has('redirect_uri')) {
            $referer =Cookie::get('redirect_uri', $referer);
            Cookie::delete('redirect_uri');
        }
        return $referer?:null;
    }

    public function setForward(string $url) {
        Cookie::set('redirect_uri',$url);
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

    /**
     * 使用Etag
     * 注意：请不要再输出内容
     *
     * @param string $etag
     * @return void
     */
    public static function etag(string $etag)
    {
        self::setHeader('Etag:'.$etag);
        $request=Request::getInstance();
        if ($str=$request->getHeader('If-None-Match')) {
            if (strcasecmp($etag, $str)===0) {
                self::state(304);
                self::close();
                return true;
            }
        }
        return false;
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
            self::$mime=parse_ini_file(SYSTEM_RESOURCE.'/mime.ini');
        }
        if ($name) {
            return self::$mime[$name] ?? conf('mime.'.$name, $name);
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

    public static function addHeader(string $name,string $value) {
        return self::setHeader(trim($name).':'.$value);
    }

    protected static function _etag(string $etag)
    {
        if (conf('app.etag', !conf('debug'))) {
            return self::etag($etag);
        }
        return false;
    }
   
    public static function statusMessage(int $state)
    {
        if (is_null(self::$status)) {
            self::$status=parse_ini_file(SYSTEM_RESOURCE.'/status.ini');
        }
        return self::$status[$state] ?? 'OK';
    }
}
