<?php
namespace suda\core;

use Exception;
use suda\template\Manager;
use suda\template\Language;

class Application
{
    protected $path;
    public function __construct(string $app)
    {
        $this->path=$app;
        // 基本常量
        defined('MODULES_DIR') or define('MODULES_DIR', APP_DIR.'/modules');
        defined('RESOURCE_DIR') or define('RESOURCE_DIR', APP_DIR.'/resource');
        defined('DATA_DIR') or define('DATA_DIR', APP_DIR.'/data');
        defined('LOG_DIR') or define('LOG_DIR', DATA_DIR.'/logs');
        defined('VIEWS_DIR') or define('VIEWS_DIR', DATA_DIR.'/views');
        defined('CACHE_DIR') or define('CACHE_DIR', RESOURCE_DIR.'/cache');
        defined('CONFIG_DIR') or define('CONFIG_DIR', RESOURCE_DIR.'/config');
        defined('TEMP_DIR') or define('TEMP_DIR', DATA_DIR.'/temp');

        // 设置PHP属性
        set_time_limit(Config::get('timelimit', 0));
        // 设置时区
        date_default_timezone_set(Config::get('timezone', 'PRC'));
        if (Storage::exist($path=CONFIG_DIR.'/config.json')) {
            Config::load($path);
        }

        if (Storage::exist($path=CONFIG_DIR.'/config.sys.json')) {
            Config::load($path);
        }
        
        if (Config::get('debug', false)) {
            Manager::loadCompile();
        }

        System::setNamespace(Config::get('app.namespace'));
    }

    public static function onRequest(Request $request)
    {
        return false;
    }
    public static function onShutdown()
    {
    }

    public static function uncaughtException(Exception $e)
    {
    }
    public static function uncaughtError(int $erron, string $error, string $file, int $line)
    {
    }
    // 激活模块
    public static function activeModule(string $module)
    {
        define('MODULE_RESOURCE', MODULES_DIR.'/'.$module.'/resource');
        define('MODULE_LANGS', MODULE_RESOURCE.'/langs');
        define('MODULE_CONFIG', MODULE_RESOURCE.'/config');
        System::addIncludePath(MODULES_DIR.'/'.$module.'/src');
        System::addIncludePath(MODULES_DIR.'/'.$module.'/libs');
        if (Storage::exist($path=MODULE_CONFIG.'/config.json')) {
            Config::set('module', Json::loadFile($path));
        }
        // 加载语言包
        if (Storage::exist($path=MODULE_LANGS.'/'.Config::get('app.language').'.json')) {
            Language::load($path);
        }
    }
}
