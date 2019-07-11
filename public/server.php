<?php

define('SUDA_APP', __DIR__ . '/../app');
define('SUDA_DATA', __DIR__ . '/../app/data');
define('SUDA_SYSTEM', __DIR__ . '/../suda');
define('SUDA_PUBLIC', __DIR__);
define('SUDA_DEBUG', true);
define('SUDA_DEBUG_LEVEL', 'info');
define('SUDA_APP_MANIFEST', SUDA_APP . '/manifest');

require_once SUDA_SYSTEM . '/loader/loader.php';


use suda\framework\debug\log\logger\FileLogger;
use suda\framework\loader\Loader;
use suda\application\builder\ApplicationBuilder;
use suda\swoole\Request;
use suda\swoole\Response;
use Swoole\Http\Server;


// 初始化系统加载器
$loader = new Loader;
$loader->register();
$loader->addIncludePath(SUDA_SYSTEM . '/src', 'suda');

$application = ApplicationBuilder::build($loader, SUDA_APP, SUDA_APP_MANIFEST, SUDA_DATA);

$application->registerDebugger();

$logger = new FileLogger(
    [
        'log-level' => SUDA_DEBUG_LEVEL,
        'save-path' => $application->getDataPath() . '/logs',
        'save-zip-path' => $application->getDataPath() . '/logs/zip',
        'log-format' => '%message%',
        'save-pack-path' => $application->getDataPath() . '/logs/dump',
    ]
);


$application->getDebug()->setLogger($logger);

$http = new Server('127.0.0.1', 9501);

$http->on('request', function ($request, $response) use ($application, $logger) {
    $application->getDebug()->applyConfig([
        'start-time' => defined('SUDA_START_TIME') ? constant('SUDA_START_TIME') : microtime(true),
        'start-memory' => defined('SUDA_START_MEMORY') ? constant('SUDA_START_MEMORY') : memory_get_usage(),
    ]);
    $application->run(new Request($request), new Response($response));
    $logger->write();
});

$http->start();


