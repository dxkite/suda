<?php    
    // App所在目录
    // 其中，app为固定的名词，尽量不要更改。
    // 否则当使用系统控制台(system/console)
    // 需要更改相应的目录表示
    // 当系统和应用处在同一目录下时，请保证APP_DIR的一致性
    define('APP_DIR',__DIR__.'/../app');
    // 系统所在目录
    define('SYSTEM',__DIR__.'/../system/');
    define('DEBUG',true);
    require_once SYSTEM.'/suda.php';