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
        defined('MODULES_DIR') or define('MODULES_DIR', Storage::path(APP_DIR.'/modules'));
        defined('RESOURCE_DIR') or define('RESOURCE_DIR', Storage::path(APP_DIR.'/resource'));
        defined('DATA_DIR') or define('DATA_DIR', Storage::path(APP_DIR.'/data'));
        defined('LOG_DIR') or define('LOG_DIR', Storage::path(DATA_DIR.'/logs'));
        defined('VIEWS_DIR') or define('VIEWS_DIR', Storage::path(DATA_DIR.'/views'));
        defined('CACHE_DIR') or define('CACHE_DIR', Storage::path(DATA_DIR.'/cache'));
        defined('CONFIG_DIR') or define('CONFIG_DIR', Storage::path(RESOURCE_DIR.'/config'));
        defined('TEMP_DIR') or define('TEMP_DIR', Storage::path(DATA_DIR.'/temp'));
        defined('SHRAE_DIR') or define('SHRAE_DIR', Storage::path(APP_DIR.'/share'));
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
        System::addIncludePath(SHRAE_DIR);
        if ($modules=Config::get('app.modules')) {
            foreach ($modules as $module) {
                if (Storage::isDir(MODULES_DIR.'/'.$module.'/share')){
                    System::addIncludePath(MODULES_DIR.'/'.$module.'/share');
                }
            }
        }
    }

    // 激活模块
    public static function activeModule(string $module)
    {
        define('MODULE_RESOURCE', Storage::path(MODULES_DIR.'/'.$module.'/resource'));
        define('MODULE_LANGS', Storage::path(MODULE_RESOURCE.'/langs'));
        define('MODULE_CONFIG', Storage::path(MODULE_RESOURCE.'/config'));
        System::addIncludePath(Storage::path(MODULES_DIR.'/'.$module.'/src'));
        System::addIncludePath(Storage::path(MODULES_DIR.'/'.$module.'/share'));
        if (Storage::exist($path=MODULE_CONFIG.'/config.json')) {
            Config::set('module', Json::loadFile($path));
        }
        // 加载语言包
        if (Config::get('app.language') && Storage::exist($path=MODULE_LANGS.'/'.Config::get('app.language').'.json')) {
            Language::load($path);
        }
    }


    public static function onRequest(Request $request)
    {
        return true;
    }
    
    public static function onShutdown()
    {
    }

    public static function uncaughtException($e)
    {
        return false;
    }
    public static function uncaughtError($erron, $error, $file, $line)
    {
        return false;
    }
}
