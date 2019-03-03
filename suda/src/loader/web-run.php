<?php

use suda\framework\Event;
use suda\framework\Config;
use suda\framework\Server;
use suda\framework\Request;
use suda\framework\Debugger;
use suda\framework\Container;
use suda\framework\runnable\Runnable;
use suda\framework\filesystem\FileSystem;
use suda\framework\debug\log\logger\FileLogger;
use suda\framework\debug\log\logger\NullLogger;
use suda\framework\http\Request as HTTPRequest;

require_once __DIR__ .'/loader.php';

Server::$container = new Container;
Server::$container->setSingle('loader', $loader);
Server::$container->setSingle('config', Config::class);
Server::$container->setSingle('event', Event::class);

Server::$container->setSingle('request', function() {
    return new Request(HTTPRequest::create());
});

Server::$container->setSingle('debug', function () {
    $logger = (new Runnable(Server::$container->get('config')->get('app.logger-build', new Runnable(
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
    ))))->run();

    $debugger = new Debugger;
    
    // $debugger->addAttribute('remote-ip', $this->getRequest()->ip());
    // $debugger->addAttribute('debug', $this->isDebug());
    // $debugger->addAttribute('request-uri', $this->getRequest()->getUrl());
    // $debugger->addAttribute('request-method', $this->getRequest()->getMethod());
    
    $debugger->addAttribute('request-time', date('Y-m-d H:i:s', \constant('SUDA_START_TIME')));

    $debugger->applyConfig([
        'start-time' => \constant('SUDA_START_TIME'),
        'start-memory' => \constant('SUDA_START_MEMORY'),
    ]);
    
    $debugger->setLogger($logger);
    $logger->notice(PHP_EOL.'{request-time} {remote-ip} {request-method} {request-uri} debug={debug}', $debugger->getAttribute());
    return $debugger;
});

Server::$container->get('debug')->notice('system start running');
