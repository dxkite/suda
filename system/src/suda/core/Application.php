<?php
/**
 * Suda FrameWork
 *
 * An open source application development framework for PHP 7.0.0 or newer
 * 
 * Copyright (c)  2017 DXkite
 *
 * @category   PHP FrameWork
 * @package    Suda
 * @copyright  Copyright (c) DXkite
 * @license    MIT
 * @link       https://github.com/DXkite/suda
 * @version    since 1.2.4
 */
namespace suda\core;

use suda\template\Manager;
use suda\tool\Json;
use suda\tool\ArrayHelper;
use suda\exception\ApplicationException;
use suda\exception\JSONException;

class Application
{
    // app 目录
    private $path;
    // 当前模块名
    private static $active_module;
    // 激活的模块
    private static $module_live=null;
    // 模块配置
    private static $module_configs=null;
    // 模块名缓存
    private static $module_name_cache=[];
    // 模块目录装换成模块名
    private static $module_dir_name=[];
    private static $modules_path=[];

    public function __construct(string $app)
    {
        debug()->trace(__('application load %s', $app));
        $this->path=$app;
    
        // 获取基本配置信息
        if (Storage::exist($path=CONFIG_DIR.'/config.json')) {
            try {
                Config::load($path);
            } catch (JSONException $e) {
                debug()->die(__('parse application config.json error'));
            }
            // 开发状态覆盖
            if (defined('DEBUG')) {
                Config::set('debug', DEBUG);
                Config::set('app.debug', DEBUG);
            }
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
        // 设置默认命名空间
        Autoloader::setNamespace(Config::get('app.namespace'));
        // 系统共享库
        Autoloader::addIncludePath(SHRAE_DIR);
        // 注册模块目录
        Application::addModulesPath(SYSTEM_RESOURCE.'/modules');
        Application::addModulesPath(MODULES_DIR);
        // 读取目录，注册所有模块
        Application::registerModules();
        // 加载模块
        self::loadModules();
        // 调整模板
        Manager::theme(conf('app.template', 'default'));
        Hook::exec('Application:init');
        // 初次运行初始化资源
        if (conf('app.init')) {
            init_resource();
        }
    }

    /**
     * 添加模块扫描目录
     *
     * @param string $path
     * @return void
     */
    public static function addModulesPath(string $path)
    {
        $path=Storage::abspath($path);
        if ($path && !in_array($path, self::$modules_path)) {
            self::$modules_path[]=$path;
        }
    }

    /**
     * 载入模块
     *
     * @return void
     */
    public static function loadModules()
    {
        // 模块共享库
        $module_all=self::getModules();
        // 激活模块
        $module_use=self::getLiveModules();
        // 安装 启用 活动
        foreach ($module_all as $module_temp) {
            $root=self::getModulePath($module_temp);
            // 注册模块共享目录
            if (Storage::isDir($share_path=$root.'/share')) {
                Autoloader::addIncludePath($share_path);
            }
            
            // 自动安装
            if (conf('auto-install',true)) {
                Hook::listen('Application:init', function () use ($module_temp) {
                    self::installModule($module_temp);
                });
            }

            // 是否激活
            $is_live_module=in_array($module_temp, $module_use);
            if ($is_live_module) {
                // 加载监听器
                if (Storage::exist($listener_path=$root.'/resource/config/listener.json')) {
                    Hook::loadJson($listener_path);
                }
                // 设置语言包库
                Locale::path($root.'/resource/locales/');
            }
        }
    }

    public static function installModule(string $module)
    {
        $install_lock = DATA_DIR.'/install/install_'.substr(md5($module), 0, 6).'.lock';
        storage()->path(dirname($install_lock));
        $config=self::getModuleConfig($module);
        if (isset($config['install']) && !file_exists($install_lock)) {
            $installs=$config['install'];
            if (is_string($installs)) {
                $installs=[$installs];
            }
            foreach ($installs as $cmd) {
                cmd($cmd)->args($config);
            }
            file_put_contents($install_lock, 'name='.$module."\r\n".'time='.microtime(true));
        }
    }


    /**
     * 获取所有模块
     *
     * @return void
     */
    public static function getModules()
    {
        return array_values(self::$module_dir_name);
    }

    public static function getModuleDirs()
    {
        return array_keys(self::$module_dir_name);
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

    public static function checkModuleExist(string $name)
    {
        return Application::getModuleDir($name)!=false;
    }

    public static function getLiveModules()
    {
        if (self::$module_live) {
            return self::$module_live;
        }
        $modules=conf('app.modules', self::getModules());
        $exclude=defined('DISABLE_MODULES')?explode(',', trim(DISABLE_MODULES, ',')):[];
        foreach ($exclude as $index=>$name) {
            $exclude[$index]=Application::getModuleFullName($name);
        }
        // debug()->trace('modules', json_encode($modules));
        // debug()->trace('exclude', json_encode($exclude));
        foreach ($modules as $index => $name) {
            $fullname=Application::getModuleFullName($name);
            // 剔除模块名
            if (!self::checkModuleExist($name) || in_array($fullname, $exclude)) {
                unset($modules[$index]);
            } else {
                $modules[$index]=$fullname;
            }
        }
        // 排序，保证为数组
        sort($modules);
        debug()->trace('live modules', json_encode($modules));
        return self::$module_live=$modules;
    }

    /**
     * 激活运行的模块
     *
     * @param string $module
     * @return void
     */
    public static function activeModule(string $module)
    {
        Hook::exec('Application:active', [$module]);
        debug()->trace(__('active module %s', $module));
        self::$active_module=$module;
        $root=self::getModulePath($module);
        $module_config=self::getModuleConfig($module);
        define('MODULE_RESOURCE', Storage::path($root.'/resource'));
        define('MODULE_CONFIG', Storage::path(MODULE_RESOURCE.'/config'));
        debug()->trace(__('set locale %s', Config::get('app.locale', 'zh-CN')));
        Locale::path(MODULE_RESOURCE.'/locales');
        Locale::set(Config::get('app.locale', 'zh-CN'));
        if (isset($module_config['namespace'])) {
            // 缩减命名空间
            Autoloader::setNamespace($module_config['namespace']);
        }
        // 自动加载私有库
        Autoloader::addIncludePath($root.'/src');
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
        // TODO: CACHE Appication Info
        // ArrayHelper::export(CACHE_DIR.'/module_configs.cache.php','module_configs',self::$module_configs);
        // ArrayHelper::export(CACHE_DIR.'/module_dir_name.cache.php','module_dir_name',self::$module_dir_name);
    }

    public static function uncaughtException($e)
    {
        return false;
    }

    /**
     * 获取模块名，不包含版本号
     *
     * @param string $name 不完整模块名
     * @return void
     */
    public static function getModuleName(string $name)
    {
        $name=self::getModuleFullName($name);
        return preg_replace('/:.+$/', '', $name);
    }
    
    /**
     * 获取模块全名（包括版本）
     * name:version,name,namespace/name => namespace/name:version
     * 未指定版本则调整到最优先版本
     *
     * @param string $name 不完整模块名
     * @return void
     */
    public static function getModuleFullName(string $name)
    {
        // 存在缓存则返回缓存
        if (isset(self::$module_name_cache[$name])) {
            return self::$module_name_cache[$name];
        }
        preg_match('/^(?:([a-zA-Z0-9_-]+)\/)?([a-zA-Z0-9_-]+)(?::(.+))?$/', $name, $matchname);
        $preg='/^'.(isset($matchname[1])&&$matchname[1]? preg_quote($matchname[1]).'\/':'(\w+\/)?') // 限制域
            .preg_quote($matchname[2]) // 名称
            .(isset($matchname[3])&&$matchname[3]?':'.preg_quote($matchname[3]):'(:.+)?').'$/'; // 版本号
        $targets=[];
        // debug()->debug($matchname, $preg);
        // 匹配模块名，查找符合格式的模块
        foreach (self::$module_configs as $module_name=>$module_config) {
            // 匹配到模块名
            if (preg_match($preg, $module_name)) {
                preg_match('/^(?:(\w+)\/)?(\w+)(?::(.+))?$/', $module_name, $matchname);
                // 获取版本号
                if (isset($matchname[3])&&$matchname[3]) {
                    $targets[$matchname[3]]=$module_name;
                } else {
                    $targets[]=$module_name;
                }
            }
        }
        // 排序版本
        uksort($targets, 'version_compare');
        return count($targets)>0?array_pop($targets):$name;
    }

    /**
     * 获取模块所在的文件夹名
     *
     * @param string $name
     * @return void
     */
    public static function getModuleDir(string $name)
    {
        $name=self::getModuleFullName($name);
        if (isset(self::$module_configs[$name])) {
            return self::$module_configs[$name]['directory'];
        }
        return false;
    }

    /**
     * 根据模块目录名转换成模块名
     *
     * @param string $dirname
     * @return void
     */
    public static function moduleName(string $dirname)
    {
        return self::$module_dir_name[$dirname]?:$name;
    }

    /**
     * 注册所有模块信息
     *
     * @return void
     */
    private static function registerModules()
    {
        foreach (self::$modules_path as $path) {
            $dirs=Storage::readDirs($path);
            foreach ($dirs as $dir) {
                self::registerModule($path.'/'.$dir);
            }
        }
    }

    public static function registerModule(string $path)
    {
        if (Storage::exist($file=$path.'/module.json')) {
            $dir=basename($path);
            debug()->trace(__('load module config %s', $file));
            $json=Json::parseFile($file);
            $name=$json['name'] ?? $dir;
            $json['directory']=$dir;
            $json['path']=$path;
            $name.=isset($json['version'])?':'.$json['version']:'';
            self::$module_configs[$name]=$json;
            self::$module_dir_name[$dir]=$name;
        }
    }

    public static function getModulesInfo()
    {
        return self::$module_configs;
    }

    public static function getModulePath(string $module)
    {
        $name=self::getModuleFullName($module);
        if (isset(self::$module_configs[$name])) {
            return self::$module_configs[$name]['path'];
        }
        return false;
    }

    private static function configDBify()
    {
        if (file_exists($path=RUNTIME_DIR.'/database.config.php')) {
            $config=include $path;
            Config::set('database', $config);
        }
    }
}
