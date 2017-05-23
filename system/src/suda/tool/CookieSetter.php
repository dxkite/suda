<?php
namespace suda\tool;

class CookieSetter
{
    public $name;
    public $value;
    public $httponly=false;
    public $path='/';
    public $domain=null;
    public $expire=0;
    public $secure=false;
    public $session=false;
    public $fulltime=false;

    public function __construct(string $name, string $value, int $expire=0)
    {
        $this->name=$name;
        $this->value=$value;
        $this->expire=$expire;
    }
    public function httpOnly(bool $set=true)
    {
        $this->httponly=$set;
        return $this;
    }

    public function full(bool $set=true){
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
        // 检测请求头发送情况
        $send=headers_sent($file, $line);
        if ($send) {
            _D()->waring(__('请求头在文件%s#%d时已经发送！', $file, $line));
        } else {
            $time= $this->fulltime ? $this->expire : time()+$this->expire;
            $expire= $this->session ? 0 : $this->expire;
            return setcookie($this->name, $this->value, $expire, $this->path, $this->domain, $this->secure, $this->httponly);
        }
    }
}
