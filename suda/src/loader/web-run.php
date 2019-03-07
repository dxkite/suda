<?php


use suda\framework\Event;
use suda\framework\Route;
use suda\framework\Config;
use suda\framework\Context;
use suda\framework\Request;
use suda\framework\Service;
use suda\framework\Debugger;
use suda\framework\Response;

use suda\application\loader\ApplicationLoader;
use suda\framework\http\Request as HTTPRequest;
use suda\application\builder\ApplicationBuilder;

require_once __DIR__ .'/loader.php';

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

$appLoader = new ApplicationLoader(ApplicationBuilder::build(SUDA_APP), $context);

$appLoader->load();


$service->on('service:load-route', function ($route) {
    $route->get('index', '/', function ($request, $response) use ($route) {
        return 'hello, index';
    });
    
    $route->get('hello', '/helloworld', function ($request, $response) use ($route) {
        return 'hello world <strong>' . $route->create('hello', ['name' => 'dxkite']).'</strong>';
    });
    
    $route->get('exception', '/exception', function ($request, $response) use ($route) {
        throw new \Exception('some exception!');
    });
});

$service->run();

$context->get('debug')->notice('system shutdown');
var_dump($appLoader);
exit;
