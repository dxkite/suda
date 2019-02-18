<?php
namespace suda\framework;

use suda\framework\server\Config;

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
    
    public static function request()
    {
    }

    public static function response()
    {
    }
}
