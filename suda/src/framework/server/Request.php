<?php
namespace suda\framework\server;

use suda\framework\server\RequestWrapper;

class Request extends RequestWrapper
{
   
    /**
     * 静态实例
     *
     * @var self
     */
    protected static $instance;


    protected function __construct()
    {
        $this->loadFromServer();
    }

    /**
     * 返回实例
     *
     * @return self
     */
    public static function instance()
    {
        if (isset(static::$instance)) {
            return static::$instance;
        }
        return static::$instance = new static;
    }

    /**
     * 附加属性
     *
     * @var array
     */
    protected $attribute;

    /**
     * 获取请求属性
     *
     * @return  mixed
     */
    public function getAttribute(string $name)
    {
        return $this->attribute[$name] ?? null;
    }

    /**
     * 设置请求属性
     *
     * @param string $name
     * @param mixed $attribute
     * @return self
     */
    public function setAttribute(string $name, $attribute)
    {
        $this->attribute[$name] = $attribute;

        return $this;
    }


}
