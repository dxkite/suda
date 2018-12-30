<?php
/**
 * Suda FrameWork
 *
 * An open source application development framework for PHP 7.2.0 or newer
 *
 * Copyright (c)  2017-2018 DXkite
 *
 * @category   PHP FrameWork
 * @package    Suda
 * @copyright  Copyright (c) DXkite
 * @license    MIT
 * @link       https://github.com/DXkite/suda
 * @version    since 1.2.4
 */
namespace suda\core;

use suda\core\Hook;
use suda\core\Config;
use suda\core\Module;
use suda\core\Storage;
use suda\tool\ZipHelper;
use suda\core\Autoloader;
use suda\template\Manager;
use suda\tool\ArrayHelper;
use suda\exception\ApplicationException;

/**
 * 应用处理类
 *
 * 包含了应用的各种处理方式，可以用快捷函数 app() 来使用本类
 *
 */
class Application extends Module
{
    /**
     * app 目录
     *
     * @var string
     */
    private $path;

    /**
     * 当前模块名
     *
     * @var string
     */
    private $activeModule;

    /**
     * 激活的模块
     *
     * @var array|null
     */
    private $moduleLive=null;

    /**
     * 启用路由的模块
     *
     * @var array|null
     */
    private $routeReachable = null;

    /**
     * App模块目录
     *
     * @var array
     */
    private $modulesPath=[];

    protected static $instance;

    protected function __construct()
    {
        debug()->trace(__('application load $0', APP_DIR));
        // 框架依赖检测
        if (!static::versionCompire(Config::get('app.suda', SUDA_VERSION), SUDA_VERSION)) {
            suda_panic('ApplicationException', __('application require suda version $0 and now is $1', Config::get('app.suda'), SUDA_VERSION));
        }
        $this->path=APP_DIR;
        // 获取基本配置信息
        if ($path=Config::resolve(CONFIG_DIR.'/config.json')) {
            try {
                Config::load($path);
            } catch (\Exception $e) {
                $message =__('load application config: parse config error');
                debug()->error($message);
                suda_panic('Kernal Panic', $message);
            }
            // 动态配置覆盖
            if ($path=Config::resolve(RUNTIME_DIR.'/global.config.php')) {
                Config::load($path);
            }
            // 开发状态覆盖
            if (defined('DEBUG')) {
                Config::set('debug', DEBUG);
                Config::set('app.debug', DEBUG);
            }
        }
        // 加载外部数据库配置
        $this->configDBify();
        // 监听器
        if ($path=Config::resolve(CONFIG_DIR.'/listener.json')) {
            Hook::loadConfig($path);
        }
        // 设置PHP属性
        set_time_limit(Config::get('timelimit', 0));
        // 设置时区
        date_default_timezone_set(Config::get('timezone', defined('DEFAULT_TIMEZONE')?DEFAULT_TIMEZONE:'PRC'));
        // 设置默认命名空间
        if ($namespace=Config::get('app.namespace')) {
            Autoloader::setNamespace($namespace);
        }
        // 注册模块目录
        $this->addModulesPath(SYSTEM_RESOURCE.'/modules');
        $this->addModulesPath(MODULES_DIR);
    }

    public static function getInstance()
    {
        $name=Config::get('app.application', Application::class);
        // var_dump($name);
        if (is_null(self::$instance)) {
            self::$instance=new $name();
        }
        return self::$instance;
    }

    /**
     * 载入模块
     *
     * @return void
     */
    protected function loadModules()
    {
        // 激活模块
        $moduleLive=$this->getLiveModules();
        // 安装、启用使用的模块
        foreach ($moduleLive as $module) {
            $this->loadModule($module);
             // 自动安装
            if (conf('auto-install', true)) {
                Hook::listen('suda:application:init', function () use ($module) {
                    $this->installModule($module);
                });
            }
        }
        Hook::exec('suda:module:load');
    }
    
    protected function initDatabase()
    {
        // 自动创建数据库
        if (conf('database.create', conf('debug')) && !storage()->exist($path = CACHE_DIR.'/database/auto-create-database/'.conf('database.name'))) {
            $status = query('CREATE DATABASE IF NOT EXISTS `'.conf('database.name').'` CHARACTER SET utf8mb4')->exec();
            storage()->path(dirname($path));
            storage()->put($path, conf('database.name').':'.$status);
            debug()->info(__('auto created database $0', conf('database.name')));
        }
    }
    
    public function init()
    {
        // 读取目录，注册所有模块
        $this->registerModules();
        // 自动创建数据库
        $this->initDatabase();
        // 加载模块
        $this->loadModules();
        // 调整模板
        Manager::theme(conf('app.template', 'default'));
        // 初次运行初始化资源
        if (conf('app.init')) {
            init_resource();
        }
        Hook::exec('suda:application:init');
        Locale::path($this->path.'/resource/locales/');
        hook()->listen('suda:route:dispatch::before', [$this, 'onRequest']);
        hook()->listen('suda:system:shutdown', [$this, 'onShutdown']);
        hook()->listen('suda:system:exception::display', [$this,'uncaughtException']);
    }

    /**
     * 安装有自动安装功能的模块
     *
     * @param string $module
     * @return void
     */
    public function installModule(string $module)
    {
        $config = $this->getModuleConfig($module);
        $installName = $this->getModuleFullName($module);
        $installLock = DATA_DIR.'/install/install_'.substr(md5($installName), 0, 6).'.lock';
        storage()->path(dirname($installLock));
        if (array_key_exists('install', $config) && !file_exists($installLock)) {
            $installs=$config['install'];
            if (is_string($installs)) {
                $installs=[$installs];
            }
            foreach ($installs as $cmd) {
                cmd($cmd)->args($config);
            }
            file_put_contents($installLock, 'name='.$module.PHP_EOL.'time='.microtime(true));
        }
    }

    /**
     * 获取当前激活的模块
     *
     * @return string
     */
    public function getActiveModule():?string
    {
        return $this->activeModule;
    }
 

    /**
     * 获取app/resource/config下的配置
     *
     * ```php
     * app()->getConfig(文件名);
     * ```
     *
     * @param string $configName
     * @return array|null
     */
    public function getConfig(string $configName):?array
    {
        if ($path = Config::resolve(CONFIG_DIR .'/'.$configName)) {
            return Config::loadConfig($path);
        }
        return null;
    }

    /**
     * 获取激活的模块
     *
     * @return array
     */
    public function getLiveModules()
    {
        if (!is_null($this->moduleLive)) {
            return $this->moduleLive;
        }
        if (file_exists($path=RUNTIME_DIR.'/modules.config.php')) {
            $modules=include $path;
        } else {
            $modules=conf('app.modules', $this->getModules());
        }
        $exclude=defined('DISABLE_MODULES')?explode(',', trim(\DISABLE_MODULES, ',')):[];
        foreach ($exclude as $index=>$name) {
            $exclude[$index]=$this->getModuleFullName($name);
        }
        foreach ($modules as $index => $name) {
            $fullname=$this->getModuleFullName($name);
            // 剔除模块名
            if (!$this->checkModuleExist($name) || in_array($fullname, $exclude)) {
                unset($modules[$index]);
            } else {
                $modules[$index]=$fullname;
            }
        }
        sort($modules);
        debug()->trace('live modules', json_encode($modules));
        return $this->moduleLive=$modules;
    }

    /**
     * 获取网页端可达的模块
     *
     * @return array
     */
    public function getReachableModules()
    {
        if (is_null($this->routeReachable)) {
            $this->routeReachable = $this->getConfigReachableModule();
        }
        return $this->routeReachable;
    }

    /**
     * 添加可达模块
     *
     * @param string $name
     * @return void
     */
    public function addReachableModule(string $name)
    {
        if (is_null($this->routeReachable)) {
            $this->routeReachable = $this->getConfigReachableModule();
        }
        $this->routeReachable [] = $this->getModuleFullName($name);
    }

    protected function getConfigReachableModule() {
        $liveModules = $this->getLiveModules();
        if (file_exists($path=RUNTIME_DIR.'/reachable.config.php')) {
            $modules=include $path;
        } else {
            $modules=conf('app.reachable', $liveModules);
        }
        $exclude=defined('UNREACHABLE_MODULES')?explode(',', trim(UNREACHABLE_MODULES, ',')):[];
        foreach ($exclude as $index=>$name) {
            $exclude[$index]=$this->getModuleFullName($name);
        }
        foreach ($modules as $index => $name) {
            $fullname=$this->getModuleFullName($name);
            // 剔除模块名
            if (!$this->checkModuleExist($name) || in_array($fullname, $exclude)) {
                unset($modules[$index]);
            } elseif (in_array($fullname, $liveModules)) {
                $modules[$index]=$fullname;
            }
        }
        // sort($modules);
        debug()->trace('reachable modules', json_encode($modules));
        return $modules;
    }

    /**
     * 判断模块是否可达
     *
     * @param string $name
     * @return boolean
     */
    public function isModuleReachable(string $name):bool
    {
        return in_array($this->getModuleFullName($name), $this->getReachableModules());
    }

    /**
     * 激活运行的模块
     *
     * @param string $module
     * @return boolean
     */
    public function activeModule(string $module):bool
    {
        // 不允许激活不可访问模块
        if (!$this->isModuleReachable($module)) {
            return false;
        }
        Hook::exec('suda:application:active', [$module]);
        debug()->trace(__('active module $0', $module));
        $this->activeModule=$module;
        $root=$this->getModulePath($module);
        $moduleConfig=$this->getModuleConfig($module);
        // 注入常量
        define('MODULE_NAME', $module);
        define('MODULE_PATH', Storage::path($root));
        define('MODULE_RESOURCE', Storage::path($root.'/resource'));
        define('MODULE_CONFIG', Storage::path(MODULE_RESOURCE.'/config'));
        // 加载语言配置项
        debug()->trace(__('set locale $0', Config::get('app.language', 'zh-CN')));
        Locale::path(MODULE_RESOURCE.'/locales');
        Locale::set(Config::get('app.language', 'zh-CN'));
        if (array_key_exists('namespace', $moduleConfig)) {
            // 缩减命名空间
            Autoloader::setNamespace($moduleConfig['namespace']);
        }
        // 自动加载私有库
        foreach ($moduleConfig['import']['src'] as $namespace=>$path) {
            if (Storage::isDir($srcPath=$root.DIRECTORY_SEPARATOR.$path)) {
                Autoloader::addIncludePath($srcPath, $namespace);
            } elseif (Storage::isFile($importPath=$root.DIRECTORY_SEPARATOR.$path)) {
                Autoloader::import($importPath);
            }
        }
        Config::set('module', $moduleConfig);
        Config::set('module-config', $this->getModuleConfig($module, 'config'));
        return true;
    }

    /**
     * 截获请求，请求发起的时候会调用
     *
     * @param Request $request
     * @return boolean true 表示请求可达,false将截获请求
     */
    public function onRequest(Request $request)
    {
        return true;
    }
    
    /**
     * 请求关闭的时候会调用
     *
     * @return boolean
     */
    public function onShutdown()
    {
        return true;
    }

    /**
     * 请求发生异常的时候会调用
     *
     * @return boolean
     */
    public function uncaughtException($e)
    {
        return false;
    }

    /**
     * 添加模块扫描目录
     *
     * @param string $path
     * @return void
     */
    public function addModulesPath(string $path)
    {
        $path=Storage::abspath($path);
        if ($path && !in_array($path, $this->modulesPath)) {
            $this->modulesPath[]=$path;
        }
    }
    /**
     * 注册所有模块信息
     *
     * @return void
     */
    protected function registerModules()
    {
        foreach ($this->modulesPath as $path) {
            $modulePaths = Storage::readPath($path);
            foreach ($modulePaths as $modulePath) {
                if (Storage::isFile($modulePath)) {
                    $extension = pathinfo($modulePath, PATHINFO_EXTENSION);
                    if (
                        $extension !== 'mod' &&
                        $extension !== 'module' &&
                        $extension !== 'suda-module') {
                        continue;
                    }
                }
                $this->registerModule($modulePath);
            }
        }
    }

    private function configDBify()
    {
        if (file_exists($path=RUNTIME_DIR.'/database.config.php')) {
            $config=include $path;
            Config::set('database', $config);
        }
    }
}
