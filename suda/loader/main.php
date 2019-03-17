<?php
use suda\framework\Response;
use suda\framework\loader\Path;
use suda\framework\http\Request as HttpRequest;
use suda\framework\Request;
use suda\framework\loader\Loader;
use suda\application\builder\ApplicationBuilder;

require_once __DIR__ .'/loader.php';

// 初始化系统加载器
$loader = new Loader;
$loader->register();
$loader->addIncludePath(SUDA_SYSTEM .'/src', 'suda');
// 初始化数据目录
defined('SUDA_DATA') or define('SUDA_DATA', Path::toAbsolutePath('~/data'));
$applciation = ApplicationBuilder::build($loader, SUDA_APP);
$applciation->prepare();
$applciation->run(new Request(HttpRequest::create()), new Response);
exit;
