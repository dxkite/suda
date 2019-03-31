<?php
namespace suda\framework\http;

use suda\framework\http\Response;

class Cookie implements \JsonSerializable
{
    /**
     * 名称
     *
     * @var string
     */
    protected $name;
    
    /**
     * 值
     *
     * @var string
     */
    protected $value;

    /**
     * 设置 HttpOnly
     *
     * @var boolean
     */
    protected $httpOnly = false;

    /**
     * Cookie Path
     *
     * @var string
     */
    protected $path = '/';

    /**
     * 设置域
     *
     * @var string|null
     */
    protected $domain = null;

    /**
     * 设置过期时间
     *
     * @var int
     */
    protected $expire = 0;

    /**
     * 是否使用HTTPS
     *
     * @var boolean
     */
    protected $secure = false;

    /**
     * 会话过期时间
     *
     * @var boolean
     */
    protected $session = false;

    private $fulltime = true;

    public function __construct(string $name, string $value, int $expire = 0)
    {
        $this->name = $name;
        $this->value = $value;
        $this->expire = $expire;
    }
    
    /**
     * 设置 HTTP Only
     *
     * @param boolean $set
     * @return self
     */
    public function httpOnly(bool $set = true)
    {
        $this->httpOnly = $set;
        return $this;
    }

    /**
     * 时长全部
     *
     * @param boolean $set
     * @return self
     */
    public function full(bool $set = true)
    {
        $this->fulltime = $set;
        return $this;
    }

    /**
     * 设置安全模式
     *
     * @param boolean $set
     * @return self
     */
    public function secure(bool $set = true)
    {
        $this->secure = $set;
        return $this;
    }

    /**
     * 设置路径
     *
     * @param string $set
     * @return self
     */
    public function path(string $set = '/')
    {
        $this->path = $set;
        return $this;
    }

    /**
     * 设置过期时间
     *
     * @param integer $time
     * @return self
     */
    public function expire(int $time = 1440)
    {
        $this->expire = $time;
        return $this;
    }

    /**
     * 设置cookie域
     *
     * @param string $set
     * @return self
     *
     */
    public function domain(string $set)
    {
        $this->domain = $set;
        return $this;
    }

    /**
     * 获取值
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
    
    /**
     * Get the value of name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get 设置 HttpOnly
     *
     * @return  boolean
     */
    public function isHttpOnly():bool
    {
        return $this->httpOnly;
    }

    /**
     * Get 设置域
     *
     * @return  string|null
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Get cookie Path
     *
     * @return  string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Get 设置过期时间
     *
     * @return  int
     */
    public function getExpire()
    {
        return $this->expire;
    }

    /**
     * Get 是否使用HTTPS
     *
     * @return  boolean
     */
    public function isSecure():bool
    {
        return $this->secure;
    }

    /**
     * Get 会话过期时间
     *
     * @return  boolean
     */
    public function isSession():bool
    {
        return $this->session;
    }

    /**
     * Get the value of fulltime
     */
    public function isFulltime():bool
    {
        return $this->fulltime;
    }
    
    /**
     * 设置Session
     *
     * @param boolean $session
     * @return self
     */
    public function session(bool $session = true)
    {
        $this->session = $session;
        return $this;
    }
    
    /**
     * 发送COOKIE
     *
     * @return void
     */
    public function send(Response $response)
    {
        $response->header('Set-Cookie', $this);
    }

    /**
     * 发送文本
     *
     * @return string
     */

    public function __toString()
    {
        $cookie = sprintf('%s=%s', $this->name, $this->value);

        if ($this->expire !== 0) {
            $time = $this->fulltime ? $this->expire : time() + $this->expire;
            $dateTime = \DateTime::createFromFormat('U', $this->expire, new \DateTimeZone('GMT'));
            $cookie .= '; expires='.str_replace('+0000', '', $dateTime->format('D, d M Y H:i:s T'));
        }

        if ($this->domain !== null) {
            $cookie .= '; domain='.$this->domain;
        }

        if ($this->path) {
            $cookie .= '; path='.$this->path;
        }

        if ($this->secure) {
            $cookie .= '; secure';
        }

        if ($this->httpOnly) {
            $cookie .= '; httponly';
        }
        return $cookie;
    }

    public function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'value' => $this->value,
        ];
    }
}
