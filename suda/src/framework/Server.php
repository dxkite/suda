<?php
namespace suda\framework;

use suda\framework\server\Config;
use suda\framework\server\Request;

class Server
{
    /**
     * SERVER配置
     *
     * @return Config
     */
    public static function config():Config
    {
        return Config::instance();
    }
    
    /**
     * 获取请求
     *
     * @return Request
     */
    public static function request():Request
    {
        return Request::instance();
    }

    public static function response()
    {
    }
}
