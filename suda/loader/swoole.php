<?php
require_once __DIR__ . '/loader.php';

use Swoole\Http\Server;
use suda\swoole\Request;
use suda\swoole\Response;
use suda\framework\loader\Loader;
use suda\framework\debug\log\logger\FileLogger;
use suda\application\builder\ApplicationBuilder;


defined('SUDA_SWOOLE_IP') or define('SUDA_SWOOLE_IP', '127.0.0.1');
defined('SUDA_SWOOLE_PORT') or define('SUDA_SWOOLE_PORT', 9501);


// 初始化系统加载器
$loader = new Loader;
$loader->register();
$loader->addIncludePath(SUDA_SYSTEM . '/src', 'suda');

// 创建应用
$application = ApplicationBuilder::build($loader, SUDA_APP, SUDA_APP_MANIFEST, SUDA_DATA);

// 注册Debug工具
$application->registerDebugger();

// 不复制资源
$application->config()->set('copy-static-source', false);

// 文件日志记录
$logger = new FileLogger(
    [
        'log-level' => SUDA_DEBUG_LEVEL,
        'save-path' => $application->getDataPath() . '/logs',
        'save-zip-path' => $application->getDataPath() . '/logs/zip',
        'save-dump-path' => $application->getDataPath() . '/logs/dump',
        'log-format' => '%message%',
    ]
);


$application->getDebug()->setLogger($logger);

$http = new Server(SUDA_SWOOLE_IP, SUDA_SWOOLE_PORT);

$http->on('request', function ($request, $response) use ($application, $logger) {
    $application->getDebug()->applyConfig([
        'start-time' => microtime(true),
        'start-memory' => memory_get_usage(),
    ]);
    $application->run(new Request($request), new Response($response));
    $logger->write();
});

$http->start();


