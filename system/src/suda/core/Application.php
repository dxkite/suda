<?php
namespace suda\core;

use Exception;
use suda\template\Manager;
use suda\tool\Json;
use suda\tool\ArrayHelper;
use suda\exception\ApplicationException;

class Application
{
    protected $path;
    protected static $active_module;
    protected static $module_dirs=null;
    protected static $module_cache;
    protected static $module_live=null;
    protected static $module_configs=[];
    public function __construct(string $app)
    {
        $this->path=$app;
        // 基本常量
        defined('MODULES_DIR') or define('MODULES_DIR', Storage::path(APP_DIR.'/modules'));
        defined('RESOURCE_DIR') or define('RESOURCE_DIR', Storage::path(APP_DIR.'/resource'));
        defined('DATA_DIR') or define('DATA_DIR', Storage::path(APP_DIR.'/data'));
        defined('LOG_DIR') or define('LOG_DIR', Storage::path(DATA_DIR.'/logs'));
        defined('RUNTIME_DIR') or define('RUNTIME_DIR', Storage::path(DATA_DIR.'/runtime'));
        defined('VIEWS_DIR') or define('VIEWS_DIR', Storage::path(DATA_DIR.'/views'));
        defined('CACHE_DIR') or define('CACHE_DIR', Storage::path(DATA_DIR.'/cache'));
        defined('CONFIG_DIR') or define('CONFIG_DIR', Storage::path(RESOURCE_DIR.'/config'));
        defined('TEMP_DIR') or define('TEMP_DIR', Storage::path(DATA_DIR.'/temp'));
        defined('SHRAE_DIR') or define('SHRAE_DIR', Storage::path(APP_DIR.'/share'));
        
        // 解析模块
        self::moduleMap();
        // 获取基本配置信息
        if (Storage::exist($path=CONFIG_DIR.'/config.json')) {
            Config::load($path);
        }
        
        // 加载外部数据库配置
        self::configDBify();

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
        $module_dirs=self::getModuleDirs();

        $module_use=self::getLiveModules();
        // 安装 启用 活动
        foreach ($module_dirs as $module_dir) {
            if (Storage::isDir(MODULES_DIR.'/'.$module_dir.'/share')) {
                Autoloader::addIncludePath(MODULES_DIR.'/'.$module_dir.'/share');
            }
            
            if (in_array(self::moduleName($module_dir), $module_use)) {
                // 加载监听器
                if (Storage::exist($path=MODULES_DIR.'/'.$module_dir.'/resource/config/listener.json')) {
                    Hook::loadJson($path);
                }
                // 设置语言包库
                Locale::path(MODULES_DIR.'/'.$module_dir.'/resource/locales/');
            }
        }
        Hook::exec('Application:init');
    }

    public static function getModules()
    {
        if (is_null(self::$module_dirs)) {
            // 解析模块
            self::moduleMap();
        }
        return array_keys(self::$module_dirs);
    }
    public static function getModuleDirs()
    {
        if (is_null(self::$module_dirs)) {
            // 解析模块
            self::moduleMap();
        }
        return array_values(self::$module_dirs);
    }
    public static function getActiveModule()
    {
        return self::$active_module;
    }


    public static function getModuleConfig(string $module)
    {
        return self::$module_configs[self::getModuleFullName($module)]??[];
    }

    public static function getModulePrefix(string $module)
    {
        $prefix=conf('module-prefix.'.$module, null);
        if (is_null($prefix)) {
            $prefix=self::$module_configs[self::getModuleFullName($module)]['prefix']??null;
        }
        return $prefix;
    }

    public static function checkModuleExist(string $name){
        $module_dir=Application::getModuleDir($name);
        return Storage::isDir(MODULES_DIR.'/'.$module_dir);
    }

    public static function getLiveModules()
    {
        if (self::$module_live){
            return self::$module_live;
        }
        $modules=conf('app.modules', self::getModules());
        $exclude=defined('DISALLOW_MODULES')?explode(',', trim(DISALLOW_MODULES, ',')):[];
        foreach($exclude as $index=>$name){
            $exclude[$index]=Application::getModuleFullName($name);
        }
        _D()->trace('exclude', json_encode($exclude));
        foreach ($modules as $index => $name) {
            $fullname=Application::getModuleFullName($name);
            // _D()->notice($name, 'fullname> ['.$fullname.'] name in  array exclude> ['.in_array($name, $exclude).'] fullname in  array exclude> ['.in_array($fullname, $exclude).'] exist['.self::checkModuleExist($name).']');
            if ( !self::checkModuleExist($name) || in_array($fullname, $exclude) ) {
                // _D()->notice('exclude',$exclude);
                unset($modules[$index]);
            } else{
                $modules[$index]=$fullname;
            }
        }
        sort($modules);
        _D()->trace('live modules', json_encode($modules));
        return self::$module_live=$modules;
    }

    // 激活模块
    public static function activeModule(string $module)
    {
        Hook::exec('Application:active', [$module]);
        _D()->trace(_T('active module %s', $module));
        self::$active_module=$module;
        $module_dir=self::getModuleDir($module);
        $module_config=self::getModuleConfig($module);
        define('MODULE_RESOURCE', Storage::path(MODULES_DIR.'/'.$module_dir.'/resource'));
        define('MODULE_LOCALES', Storage::path(MODULE_RESOURCE.'/locales'));
        define('MODULE_CONFIG', Storage::path(MODULE_RESOURCE.'/config'));
        _D()->trace(_T('set locale %s', Config::get('app.locale', 'zh-CN')));
        Locale::set(Config::get('app.locale', 'zh-CN'));
        if (isset($module_config['namespace'])) {
            // 缩减命名空间
            Autoloader::setNamespace($module_config['namespace']);
        }
        // 自动加载私有库
        Autoloader::addIncludePath(Storage::path(MODULES_DIR.'/'.$module_dir.'/src'));
        // 加载模块配置到 module命名空间
        if (Storage::exist($path=MODULE_CONFIG.'/config.json')) {
            Config::set('module', Json::loadFile($path));
        }
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

    public static function getModuleFullName(string $name)
    {
        return self::moduleName(self::getModuleDir($name));
    }
    /**
    * 从模块名调整到模块文件夹
    */
    public static function getModuleDir(string $name)
    {
        // 历史记录不存在
        if (is_null(self::$module_dirs)) {
            // 解析模块
            self::moduleMap();
        }
        // 存在则从缓存调用
        if (isset(self::$module_cache[$name])) {
            return self::$module_cache[$name];
        }
        // 全部匹配
        if (isset(self::$module_dirs[$name])) {
            return self::$module_dirs[$name];
        }
        // MODULE_NAME_PREG
        // 缩略匹配
        preg_match('/^(?:([a-zA-Z0-9_-]+)\/)?([a-zA-Z0-9_-]+)(?::(.+))?$/', $name, $matchname);

        _D()->debug('match name', (isset($matchname[1])&&$matchname[1]?$matchname[1]:'(\w+\/)?'));

        $preg='/^'.(isset($matchname[1])&&$matchname[1]?$matchname[1].'\/':'(\w+\/)?') // 限制域
        .preg_quote($matchname[2]). // 名称
        (isset($matchname[3])&&$matchname[3]?':'.$matchname[3]:'(:.+)?').'$/'; // 版本号
        $targets=[];
        _D()->debug($matchname, $preg);
        foreach (self::$module_dirs as $modulename=>$moduledir) {
            if (preg_match($preg, $modulename)) {
                preg_match('/^(?:(\w+)\/)?(\w+)(?::(.+))?$/', $modulename, $matchname);
                // 获取版本号
                if (isset($matchname[3])&&$matchname[3]) {
                    $targets[$matchname[3]]=$moduledir;
                } else {
                    $targets[]=$moduledir;
                }
            }
        }
        // 排序版本
        uksort($targets, 'version_compare');
        _D()->debug($targets);
        // 获取最新版本
        $dir=self::$module_cache[$name]=count($targets)>0?array_pop($targets):$name;
        _D()->trace($name.' : '.$dir);
        return $dir;
    }

    public static function moduleName(string $name)
    {
        return array_search($name, self::$module_dirs)?:$name;
    }

    public static function moduleMap()
    {
        if (Config::get('debug', false) && Storage::exist(TEMP_DIR.'/module-dir.php')) {
            self::$module_dirs=require TEMP_DIR.'/module-dir.php';
        } else {
            self::$module_dirs=self::refreshMap();
        }
    }

    protected static function refreshMap()
    {
        // [限制名/]模块名:版本号
        $dirs=Storage::readDirs(MODULES_DIR);
        $modulemap=[];
        foreach ($dirs as $dir) {
            if (Storage::exist($file=MODULES_DIR.'/'.$dir.'/module.json')) {
                $json=Json::parseFile($file);
                $name=$json['name'] ?? $dir;
                $name.=isset($json['version'])?':'.$json['version']:'';
                self::$module_configs[$name]=$json;
            } else {
                $name=$dir;
            }
            $modulemap[$name]=$dir;
        }
        
        ArrayHelper::export(TEMP_DIR.'/module-dir.php', '_module_map', $modulemap);
        return $modulemap;
    }
    public static function getModulesInfo()
    {
        return self::$module_configs;
    }

    public static function configDBify()
    {
        if (file_exists($path=RUNTIME_DIR.'/database.config.php')) {
            $config=include $path;
            Config::assign(['database'=>$config]);
        }
    }
}
