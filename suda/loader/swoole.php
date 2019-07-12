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

$http = new Server(SUDA_SWOOLE_IP, SUDA_SWOOLE_PORT);

$http->set([
    'log_file' => $logger->getConfig('save-dump-path').'/swoole.log',
]);

$http->on('request', function ($request, $response) use ($application, $logger) {
    // 拷贝副本
    $runApplication = clone $application;
    $runLogger = clone $logger;
    // 设置环境变量
    $runApplication->getDebug()->setLogger($runLogger);
    $runApplication->getDebug()->applyConfig([
        'start-time' => microtime(true),
        'start-memory' => memory_get_usage(),
    ]);
    // 注册自动加载副本
    $runApplication->getLoader()->register();
    // 运行
    $runApplication->run(new Request($request), new Response($response));
    // 写入日志
    $runLogger->write();
    // 取消自动加载副本
    $runApplication->getLoader()->unregister();
});

$http->start();
