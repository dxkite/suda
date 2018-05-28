<?php  
     // 应用所在目录
    define('APP_DIR', __DIR__.'/../app');
    // 日志所在目录
    define('APP_LOG', APP_DIR.'/data/logs');
    // 系统所在目录
    define('SYSTEM', __DIR__.'/../suda/system');
    // 网站根目录位置
    define('APP_PUBLIC', __DIR__);
    // 关闭系统管理模块
    define('DISABLE_MODULES', 'suda');
    // 开发者关闭模式
    define('DEBUG', false);
    // 错误等级
    define('LOG_LEVEL', 'error');
    // 输出日志详细信息到json文档
    define('LOG_JSON',false);
    // 输出详细信息添加到日志末尾
    define('LOG_FILE_APPEND',true);
    define('DEFAULT_TIMEZONE','PRC');
    require_once SYSTEM.'/suda.php';
