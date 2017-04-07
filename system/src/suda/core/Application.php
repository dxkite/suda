<?php
namespace suda\core;

use Exception;
use suda\template\Manager;
use suda\template\Language;
use suda\tool\Json;

class Application
{
    protected $path;
    public static $active_module;
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


        // 获取基本配置信息
        if (Storage::exist($path=CONFIG_DIR.'/config.json')) {
            Config::load($path);
        }
        
        // 系统必须配置信息
        if (Storage::exist($path=CONFIG_DIR.'/config.sys.json')) {
            Config::load($path);
        }

        // 开发时配置信息
        if (Storage::exist($path=CONFIG_DIR.'/config.dev.json')) {
            Config::load($path);
        }

        // 监听器
        if (Storage::exist($path=CONFIG_DIR.'/listener.json')) {
            Hook::loadJson($path);
        }

        // 设置PHP属性
        set_time_limit(Config::get('timelimit', 0));
        // 设置时区
        date_default_timezone_set(Config::get('timezone', 'PRC'));
        Autoloader::setNamespace(Config::get('app.namespace'));
        // 系统共享库
        Autoloader::addIncludePath(SHRAE_DIR);
        // 模块共享库
        $modules=self::getModuleDirs();
        $module_use=self::getLiveModules();
        foreach ($modules as $module_dir) {
            if (Storage::isDir(MODULES_DIR.'/'.$module_dir.'/share')) {
                Autoloader::addIncludePath(MODULES_DIR.'/'.$module_dir.'/share');
            }
            if (in_array(self::moduleName($module_dir),$module_use)){
                // 监听器
                if (Storage::exist($path=MODULES_DIR.'/'.$module_dir.'/resource/config/listener.json')) {
                    Hook::loadJson($path);
                }
            }
        }
        Hook::exec('Application:init');
    }

    public static function getModules(){
        return array_keys(conf('module-dirs',[]));
    }
    public static function getModuleDirs(){
        return array_values(conf('module-dirs',[]));
    }
    public static function getActiveModule()
    {
        return self::$active_module;
    }
    public static function getLiveModules()
    {
        return conf('app.modules',[]);
    }
    // 激活模块
    public static function activeModule(string $module)
    {
        self::$active_module=$module;
        $module_dir=conf('module-dirs.'.$module, $module);
        define('MODULE_RESOURCE', Storage::path(MODULES_DIR.'/'.$module_dir.'/resource'));
        define('MODULE_LANGS', Storage::path(MODULE_RESOURCE.'/langs'));
        define('MODULE_CONFIG', Storage::path(MODULE_RESOURCE.'/config'));
        // 自动加载私有库
        Autoloader::addIncludePath(Storage::path(MODULES_DIR.'/'.$module_dir.'/src'));
        // 加载模块配置到 module命名空间
        if (Storage::exist($path=MODULE_CONFIG.'/config.json')) {
            Config::set('module', Json::loadFile($path));
            if ($namespace=Config::get('module.namespace', false)) {
                Autoloader::setNamespace($namespace);
            }
        }
        
        // 加载监听器
        if (Storage::exist($path=MODULE_CONFIG.'/listener.json')) {
            Hook::loadJson($path);
        }

        // 加载语言包
        if (Config::get('app.language') && Storage::exist($path=MODULE_LANGS.'/'.Config::get('app.language').'.json')) {
            Language::load($path);
        }
        // 模块资源准备
        // Manager::prepareResource($module);
    }


    public function onRequest(Request $request)
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
    public static function moduleDir(string $name)
    {
        return  conf('module-dirs.'.$name, $name);
    }

    public static function moduleName(string $name)
    {
        $modules= conf('module-dirs', []);
        return array_search($name, $modules)?:$name;
    }
}
