<?php

use suda\framework\debug\log\logger\FileLogger;
use suda\framework\loader\Path;
use suda\framework\loader\Loader;
use suda\framework\http\HTTPRequest;
use suda\framework\http\HTTPResponse;
use suda\application\builder\ApplicationBuilder;

require_once __DIR__ .'/loader.php';

// 初始化系统加载器
$loader = new Loader;
$loader->register();
$loader->addIncludePath(SUDA_SYSTEM .'/src', 'suda');
// 初始化数据目录
defined('SUDA_DATA') or define('SUDA_DATA', Path::toAbsolutePath('~/data'));
defined('SUDA_APP_MANIFEST') or define('SUDA_APP_MANIFEST', SUDA_APP.'/manifest');
$application = ApplicationBuilder::build($loader, SUDA_APP, SUDA_APP_MANIFEST, SUDA_DATA);
$application->registerDebugger();
$application->getDebug()->setLogger(new FileLogger(
    [
        'log-level' => SUDA_DEBUG_LEVEL,
        'save-path' => $application->getDataPath().'/logs',
        'save-zip-path' => $application->getDataPath().'/logs/zip',
        'log-format' => '%message%',
        'save-pack-path' => $application->getDataPath().'/logs/dump',
    ]
));
$application->run(HTTPRequest::create(), new HTTPResponse);
exit;
