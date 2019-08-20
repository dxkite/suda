<?php

use suda\framework\Debugger;
use suda\framework\loader\Path;
use suda\framework\loader\Loader;
use suda\framework\debug\log\logger\FileLogger;
use suda\framework\http\HTTPRequest as Request;
use suda\application\builder\ApplicationBuilder;
use suda\framework\http\HTTPResponse as Response;

require_once __DIR__ . '/loader.php';

// 初始化系统加载器
$loader = new Loader;
$loader->register();
$loader->addIncludePath(SUDA_SYSTEM . '/src', 'suda');
// 初始化数据目录
defined('SUDA_DATA') or define('SUDA_DATA', Path::toAbsolutePath('~/data'));
defined('SUDA_APP_MANIFEST') or define('SUDA_APP_MANIFEST', SUDA_APP . '/manifest');
$application = ApplicationBuilder::build($loader, SUDA_APP, SUDA_APP_MANIFEST, SUDA_DATA);
// 日志路径
defined('SUDA_DEBUG_LOG_PATH') or define('SUDA_DEBUG_LOG_PATH', $application->getDataPath() . '/logs');
// 文件日志
$logger = new FileLogger(
    [
        'log-level' => SUDA_DEBUG_LEVEL,
        'save-path' => SUDA_DEBUG_LOG_PATH,
        'save-dump-path' => SUDA_DEBUG_LOG_PATH . '/dump',
        'save-zip-path' => SUDA_DEBUG_LOG_PATH . '/zip',
        'log-format' => '%message%',
    ]
);
// 设置调试工具
$application->setDebug(new Debugger($application, $logger));
// 调试信息
$application->getDebug()->applyConfig([
    'start-time' => defined('SUDA_START_TIME') ? constant('SUDA_START_TIME') : microtime(true),
    'start-memory' => defined('SUDA_START_MEMORY') ? constant('SUDA_START_MEMORY') : memory_get_usage(),
]);
$application->getConfig()->set('save-dump-path', SUDA_DEBUG_LOG_PATH .'/dump');
$application->getConfig()->set('response-timing', SUDA_DEBUG);
$application->run(Request::create(), new Response);
exit;
