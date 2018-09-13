<?php  
     // 应用所在目录
    define('APP_DIR', __DIR__.'/../app');
    // 日志所在目录
    define('DATA_DIR', APP_DIR.'/data');
    // 系统所在目录
    define('SYSTEM', __DIR__.'/../system');
    // 网站根目录位置
    define('APP_PUBLIC', __DIR__);
    // 不可达模块
    define('UNREACHABLE_MODULES', 'suda');
    // 开发者关闭模式
    define('DEBUG', false);
    // 错误等级
    define('LOG_LEVEL', 'info');
    // 输出日志详细信息到json文档
    define('LOG_JSON',false);
    // 输出详细信息添加到日志末尾
    define('LOG_FILE_APPEND',true);
    //输出日志
    define('DEBUG_DUMP_LOG',true);
    // 设置默认时区
    define('DEFAULT_TIMEZONE','PRC');
    require_once SYSTEM.'/suda.php';
    // require_once 'phar://'.__DIR__.'/../.suda/suda-cli.phar/suda.php';