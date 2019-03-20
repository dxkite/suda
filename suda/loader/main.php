<?php
use suda\framework\Request;
use suda\framework\Response;
use suda\framework\loader\Path;
use suda\framework\loader\Loader;
use suda\framework\http\HTTPRequest;
use suda\application\builder\ApplicationBuilder;

require_once __DIR__ .'/loader.php';

// 初始化系统加载器
$loader = new Loader;
$loader->register();
$loader->addIncludePath(SUDA_SYSTEM .'/src', 'suda');
// 初始化数据目录
defined('SUDA_DATA') or define('SUDA_DATA', Path::toAbsolutePath('~/data'));
$application = ApplicationBuilder::build($loader, SUDA_APP);
$application->prepare();
$application->run(new Request(HTTPRequest::create()), new Response);
exit;
