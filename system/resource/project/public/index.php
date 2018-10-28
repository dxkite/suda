<?php
    // 应用所在目录
    define('APP_DIR', __DIR__.'/../app');
    // 数据目录
    define('DATA_DIR', APP_DIR.'/data');
    // 网站根目录位置
    define('APP_PUBLIC', __DIR__);
    // 当前入口文件
    define('SUDA_ENTRANCE', __FILE__);
    // 不可访问的模块
    define('UNREACHABLE_MODULES', 'suda');
    // 开发者关闭模式
    define('DEBUG', false);
    // 错误等级
    define('LOG_LEVEL', 'error');
    // 输出详细信息添加到日志末尾
    define('LOG_FILE_APPEND', true);
    //输出日志
    define('DEBUG_DUMP_LOG', true);
    // 设置默认时区
    define('DEFAULT_TIMEZONE', 'PRC');

    // 系统所在目录
    define('SYSTEM', __DIR__.'/../suda/system');

    // 载入框架
    require_once SYSTEM.'/suda.php';
