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
namespace suda\tool;

final class CookieSetter
{
    private $name;
    private $value;
    private $httponly=false;
    private $path='/';
    private $domain=null;
    private $expire=0;
    private $secure=false;
    private $session=false;
    private $fulltime=true;
    private $set=false;

    public function __construct(string $name, string $value, int $expire=0)
    {
        $this->name=$name;
        $this->value=$value;
        $this->expire=$expire;
        // auto path
        // $this->path=$_SERVER['PATH_INFO'];
    }
    
    public function httpOnly(bool $set=true)
    {
        $this->httponly=$set;
        return $this;
    }

    public function full(bool $set=true)
    {
        $this->fulltime=$set;
        return $this;
    }

    public function secure(bool $set=true)
    {
        $this->secure=$set;
        return $this;
    }

    public function path(string $set='/')
    {
        $this->path=$set;
        return $this;
    }

    public function expire(int $set=1440)
    {
        $this->expire=$set;
        return $this;
    }

    public function domain(string $set)
    {
        $this->domain=$set;
        return $this;
    }

    public function get()
    {
        return $this->value;
    }

    public function session(bool $session=true)
    {
        $this->session= $session;
        return $this;
    }

    public function set()
    {
        if ($this->set) {
            return;
        }
        // 检测请求头发送情况
        $send=headers_sent($file, $line);
        if ($send) {
            debug()->warning(__('set cookie %s=%s faild cookie is already send at %s#%d', $this->name, $this->value, $file, $line));
        } else {
            $this->set=true;
            $time= $this->fulltime ? $this->expire : time()+$this->expire;
            $expire= $this->session ? 0 : $this->expire;
            return setcookie($this->name, $this->value, $expire, $this->path, $this->domain, $this->secure, $this->httponly);
        }
    }
}
