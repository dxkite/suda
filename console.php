<?php
require_once __DIR__ . '/vendor/autoload.php';

use suda\framework\Config;
use suda\application\Module;
use suda\framework\Debugger;
use suda\framework\loader\Loader;
use suda\application\ApplicationModule;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use suda\framework\debug\log\logger\FileLogger;
use suda\application\builder\ApplicationBuilder;
use suda\application\loader\ApplicationBaseLoader;

defined('SUDA_APP') or define('SUDA_APP', __DIR__ . '/app');
defined('SUDA_DATA') or define('SUDA_DATA', __DIR__ . '/data');
defined('SUDA_SYSTEM') or define('SUDA_SYSTEM', __DIR__ . '/suda');
defined('SUDA_PUBLIC') or define('SUDA_PUBLIC', __DIR__);
defined('SUDA_DEBUG') or define('SUDA_DEBUG', true);
defined('SUDA_DEBUG_LEVEL') or define('SUDA_DEBUG_LEVEL', 'debug');
defined('SUDA_APP_MANIFEST') or define('SUDA_APP_MANIFEST', SUDA_APP . '/manifest');
defined('SUDA_DEBUG_LOG_PATH') or define('SUDA_DEBUG_LOG_PATH', SUDA_DATA . '/logs');

require_once SUDA_SYSTEM . '/loader/loader.php';

// 初始化系统加载器
$loader = new Loader;
$loader->register();
$loader->addIncludePath(SUDA_SYSTEM . '/src', 'suda');
// 初始化数据目录
$manifestConfig = ApplicationBuilder::loadManifest(SUDA_APP, SUDA_APP_MANIFEST);

if (array_key_exists('import', $manifestConfig)) {
    ApplicationBuilder::importClassLoader($loader, $manifestConfig['import'], SUDA_APP);
}

$application = new ApplicationModule(SUDA_APP, $manifestConfig, $loader, SUDA_DATA);
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

$application->load();

$app = new Application();

/** @var Module $module */
foreach ($application->getModules() as $name => $module) {
    if ($path = $module->getResource()->getConfigResourcePath('config/console')) {
        $consoleConfig = Config::loadConfig($path);
        if ($consoleConfig !== null) {
            foreach ($consoleConfig as $item) {
                /** @var Command $cmd */
                $className = Loader::realName($item['class']);
                $cmd = new $className;
                $cmd->setName($module->getName() . ':' . $cmd->getName());
                $app->add($cmd);
            }
        }
    }
}

$app->run();