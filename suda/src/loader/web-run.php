<?php

use suda\framework\Event;
use suda\framework\Route;
use suda\framework\Config;
use suda\framework\Server;
use suda\framework\Context;
use suda\framework\Request;
use suda\framework\Debugger;
use suda\framework\Response;
use suda\framework\Container;
use suda\framework\Application;
use suda\framework\runnable\Runnable;
use suda\framework\filesystem\FileSystem;
use suda\framework\debug\log\LoggerInterface;
use suda\framework\debug\log\logger\FileLogger;
use suda\framework\debug\log\logger\NullLogger;
use suda\framework\http\Request as HTTPRequest;

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
    $logger = (new Runnable($context->get('config')->get(
        'app.logger-build',
        function () {
            $dataPath = SUDA_DATA.'/logs';
            FileSystem::makes($dataPath);
            if (\is_writable(dirname($dataPath))) {
                FileSystem::makes($dataPath.'/zip');
                FileSystem::makes($dataPath.'/dump');
                return new FileLogger(
                [
                    'log-level' => defined('SUDA_DEBUG_LEVEL') ? constant('SUDA_DEBUG_LEVEL') : 'debug',
                    'save-path' => $dataPath,
                    'save-zip-path' => $dataPath.'/zip',
                    'log-format' => '%message%',
                    'save-pack-path' => $dataPath.'/dump',
                ]
            );
            }
            return new NullLogger;
        }
    )))->run();

    $debugger = new Debugger;
    
    $debugger->addAttribute('remote-ip', $context->get('request')->getRemoteAddr());
    $debugger->addAttribute('debug', $context->get('config')->get('debug', false));
    $debugger->addAttribute('request-uri', $context->get('request')->getUrl());
    $debugger->addAttribute('request-method', $context->get('request')->getMethod());
    
    $debugger->addAttribute('request-time', date('Y-m-d H:i:s', \constant('SUDA_START_TIME')));

    $debugger->applyConfig([
        'start-time' => \constant('SUDA_START_TIME'),
        'start-memory' => \constant('SUDA_START_MEMORY'),
    ]);
    if ($logger instanceof LoggerInterface) {
        $debugger->setLogger($logger);
    } else {
        $debugger->setLogger(new NullLogger);
    }
    $logger->notice(PHP_EOL.'{request-time} {remote-ip} {request-method} {request-uri} debug={debug}', $debugger->getAttribute());
    return $debugger;
});

$context->get('debug')->notice('system booting');


$app = new Application($context);

$route = $context->get('route');

$route->get('index', '/', function ($request, $response) use ($route) {
    return 'hello, index';
});

$route->get('hello', '/helloworld', function ($request, $response) use ($route) {
    return 'hello world <strong>' . $route->create('hello', ['name' => 'dxkite']).'</strong>';
});

$match = $route->match($context->get('request'));

if ($match) {
    $match->run($context->get('request'), $context->get('response'));
} else {
    echo '404';
}
