<?php #1537282750

    // 应用所在目录
    define('APP_DIR', __DIR__.'/../app');
    // 日志所在目录
    define('DATA_DIR', APP_DIR.'/data');
    // 系统所在目录
    define('SYSTEM',__DIR__.'/../system');
    // 网站根目录位置
    define('APP_PUBLIC',__DIR__);
    // 开发者模式
    define('DEBUG',true);
    // 日志纪录等级
    define('LOG_LEVEL', 'trace');
    // 输出日志详细信息到json文档
    define('LOG_JSON',false);
    // 输出详细信息添加到日志末尾
    define('LOG_FILE_APPEND',true);
    define('DEFAULT_TIMEZONE','PRC');
    require_once SYSTEM.'/suda.php';