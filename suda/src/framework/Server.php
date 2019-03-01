<?php
namespace suda\framework;

use suda\framework\server\Config;
use suda\framework\server\Request;
use suda\framework\server\Response;
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
     * 请求数据
     *
     * @var Response
     */
    protected static $response;

    /**
     * 获取服务器参数
     *
     * @var array|null
     */
    protected static $server;
    
    /**
     * 解析请求
     */
    public static function parse()
    {
        static::build(Builder::create(), $_SERVER);
    }

    /**
     * 创建请求环境
     *
     * @param Request $request
     * @param array $server
     * @return void
     */
    public static function build(Request $request, array $server = null)
    {
        static::$config = new Config;
        static::$request = $request;
        static::$response = new Response(200);
        static::$server = $server;
    }


    /**
     * 获取$_SERVER数据
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $name, $default = null)
    {
        return static::$server === null ? $_SERVER[$name] ?? $default : static::$server[$name] ?? $default;
    }
    
    /**
     * 检测是否含
     *
     * @param string $name
     * @return boolean
     */
    public static function has(string $name)
    {
        return static::get($name) !== null;
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

    /**
     * 获取响应控制
     *
     * @return Response
     */
    public static function response(): Response
    {
        return static::$response;
    }
}
