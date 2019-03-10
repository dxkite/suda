<?php

use suda\Framework;
use suda\framework\loader\Path;
use suda\framework\loader\Loader;

require_once __DIR__ .'/loader.php';

// 初始化系统加载器
$loader = new Loader;
$loader->register();
$loader->addIncludePath(SUDA_SYSTEM .'/src', 'suda');
// 初始化数据目录
defined('SUDA_DATA') or define('SUDA_DATA', Path::toAbsolutePath('~/data'));
// 框架运行
Framework::bootstrap($loader);
Framework::run();
exit;
