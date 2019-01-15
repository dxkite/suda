<?php
// 加载自动加载
require_once __DIR__.'/__autoload.php';
// 初始化Debug
\suda\core\Debug::init();
// 初始化系统运行
\suda\core\System::init();
// 运行App
\suda\core\System::run();
// 结束请求
exit;
