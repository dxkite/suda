<?php #1497446012
 
    // 应用所在目录
    define('APP_DIR', __DIR__.'/../apps/wuming');
    // 系统所在目录
    define('SYSTEM', __DIR__.'/../system/');
    // 网站更目录位置
    define('APP_PUBLIC', __DIR__);
    // 关闭开发者模块
    define('DISALLOW_MODULES', 'suda');
    // 开发者关闭模式
    define('DEBUG', false);
    // 错误等级
    define('LOG_LEVEL', 'info');
    // 输出日志详细信息到json文档
    define('LOG_JSON',false);
    // 输出详细信息添加到日志末尾
    define('LOG_FILE_APPEND',false);
    // die('fuck');
    require_once SYSTEM.'/suda.php';
