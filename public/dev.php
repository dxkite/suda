<?php #1497791807
    
    // 应用所在目录
    define('APP_DIR', __DIR__.'/../app');
    // 系统所在目录
    define('SYSTEM',__DIR__.'/../system/');
    // 网站更目录位置
    define('APP_PUBLIC',__DIR__);
    // 开发者模式
    define('DEBUG',true);
    // 日志纪录等级
    define('LOG_LEVEL', 'info');
    // 输出日志详细信息到json文档
    define('LOG_JSON',true);
    // 输出详细信息添加到日志末尾
    define('LOG_FILE_APPEND',true);
    require_once SYSTEM.'/suda.php';