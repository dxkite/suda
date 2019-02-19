<?php
namespace suda\framework;

use suda\framework\server\Config;
use suda\framework\server\Request;
use suda\framework\server\request\Builder;

class Server
{
    /**
     * 静态实例
     *
     * @var self
     */
    protected static $instance;

    /**
     * 配置文件
     *
     * @var Config
     */
    protected static $config;

    /**
     * 请求数据
     *
     * @var Request
     */
    protected static $request;

   
    /**
     * 解析请求
     */
    public static function parse()
    {
        static::$config = new Config;
        static::$request = Builder::create();
    }

    /**
     * SERVER配置
     *
     * @return Config
     */
    public static function config():Config
    {
        return static::$config;
    }

    /**
     * 获取请求
     *
     * @return Request
     */
    public static function request():Request
    {
        return static::$request;
    }

    public static function response()
    {
    }
}
