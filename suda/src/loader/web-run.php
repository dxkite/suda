<?php


use suda\framework\Event;
use suda\framework\Route;
use suda\framework\Config;
use suda\framework\Context;
use suda\framework\Request;
use suda\framework\Service;
use suda\framework\Debugger;
use suda\framework\Response;
use suda\framework\loader\Path;
use suda\framework\loader\Loader;
use suda\application\loader\ApplicationLoader;
use suda\framework\http\Request as HTTPRequest;
use suda\application\builder\ApplicationBuilder;

require_once __DIR__ .'/loader.php';

// 初始化系统加载器
$loader = new Loader;
$loader->register();
$loader->addIncludePath(SUDA_SYSTEM .'/src', 'suda');

defined('SUDA_DATA') or define('SUDA_DATA', Path::toAbsolutePath('~/data'));

$context = new Context;

$context->setSingle('loader', $loader);
$context->setSingle('config', Config::class);
$context->setSingle('event', Event::class);
$context->setSingle('route', Route::class);

$context->setSingle('request', function () {
    return new Request(HTTPRequest::create());
});

$context->setSingle('response', function () {
    return new Response;
});

$context->setSingle('debug', function () use ($context) {
    return Debugger::create($context);
});

$context->get('debug')->notice('system booting');

$service = new Service($context);

$appLoader = new ApplicationLoader(ApplicationBuilder::build($context, SUDA_APP));

$service->on('service:load-config', function () use ($appLoader) {
    $appLoader->load();
});

$service->on('service:load-route', function () use ($appLoader) {
     $appLoader->loadRoute();
});

$service->run();

$context->get('debug')->notice('system shutdown');
exit;
