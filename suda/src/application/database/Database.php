<?php


namespace suda\application\database;


use suda\application\Application;

class Database
{
    /**
     * 应用引用
     *
     * @var Application
     */
    protected static $application;

    /**
     * 从应用创建表
     *
     * @param Application $application
     * @return void
     */
    public static function loadApplication(Application $application)
    {
        static::$application = $application;
    }

    /**
     * Get 应用引用
     *
     * @return  Application
     */
    public static function application()
    {
        return static::$application;
    }
}