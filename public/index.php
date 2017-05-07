<?php 
    // 应用所在目录
    define('APP_DIR', __DIR__.'/../app');
    // 系统所在目录
    define('SYSTEM', __DIR__.'/../system/');
    // 网站更目录位置
    define('APP_PUBLIC', __DIR__);
    // 关闭开发者模块
    define('DISALLOW_MODULES', 'suda');
    // 开发者关闭模式
    define('DEBUG', false);
    require_once SYSTEM.'/suda.php';
