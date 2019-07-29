<?php
require_once __DIR__ . '/loader.php';

use suda\framework\debug\log\logger\NullLogger;
use Swoole\Http\Server;
use suda\swoole\Request;
use suda\swoole\Response;
use suda\framework\Debugger;
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
// 不复制资源
$application->config()->set('copy-static-source', false);
// 日志路径
defined('SUDA_DEBUG_LOG_PATH') or define('SUDA_DEBUG_LOG_PATH', $application->getDataPath().'/logs');
// 文件日志
$logger = new FileLogger(
    [
        'log-level' => SUDA_DEBUG_LEVEL,
        'save-path' => SUDA_DEBUG_LOG_PATH,
        'save-dump-path' => SUDA_DEBUG_LOG_PATH.'/dump',
        'save-zip-path' => SUDA_DEBUG_LOG_PATH.'/zip',
        'log-format' => '%message%',
    ]
);
// Swoole 服务器
$http = new Server(SUDA_SWOOLE_IP, SUDA_SWOOLE_PORT);

$http->set([
    'log_file' => $logger->getConfig('save-dump-path').'/swoole.log',
]);

$application->config()->set('save-dump-path', SUDA_DEBUG_LOG_PATH . '/dump');
$application->config()->set('response-timing', SUDA_DEBUG);

$application->setDebug(new Debugger($application, new NullLogger()));

$http->on('request', function ($request, $response) use ($application, $logger) {
    // 拷贝副本
    $runApplication = clone $application;
    $runLogger = clone $logger;
    $runApplication->setDebug(new Debugger($runApplication, $runLogger));
    // 设置环境变量
    $runApplication->getDebug()->applyConfig([
        'start-time' => microtime(true),
        'start-memory' => memory_get_usage(),
    ]);
    // 注册自动加载副本
    $runApplication->loader()->register();
    // 运行
    $runApplication->run(new Request($request), new Response($response));
    // 写入日志
    $runLogger->write();
    // 取消自动加载副本
    $runApplication->loader()->unregister();
});

$http->start();
