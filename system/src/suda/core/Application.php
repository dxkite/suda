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
use suda\tool\ZipHelper;

/**
 * 应用处理类
 *
 * 包含了应用的各种处理方式，可以用快捷函数 app() 来使用本类
 * 
 */
class Application
{
    /**
     * app 目录
     *
     * @var [type]
     */
    private $path;
    /**
     * 当前模块名
     *
     * @var [type]
     */
    private $activeModule;
    /**
     * 激活的模块
     *
     * @var [type]
     */
    private $moduleLive=null;

    /**
     * 启用路由的模块
     *
     * @var [type]
     */
    private $routeReachable = null;
    /**
     * 模块配置
     *
     * @var [type]
     */
    private $moduleConfigs=null;
    /**
     * 模块名缓存
     *
     * @var array
     */
    private $moduleNameCache=[];
    /**
     * 模块目录装换成模块名
     *
     * @var array
     */
    private $moduleDirName=[];
    private $modulesPath=[];


    protected static $instance;

    protected function __construct()
    {
        debug()->trace(__('application load %s', APP_DIR));
        // 框架依赖检测
        if (!static::versionCompire(Config::get('app.suda', SUDA_VERSION), SUDA_VERSION)) {
            suda_panic('ApplicationException', __('application require suda version %s and now is %s', Config::get('app.suda'), SUDA_VERSION));
        }
        $this->path=APP_DIR;
        // 获取基本配置信息
        if ($path=Config::exist(CONFIG_DIR.'/config.json')) {
            try {
                Config::load($path);
            } catch (\Exception $e) {
                $message =__('Load application config: parse config error');
                debug()->error($message);
                suda_panic('Kernal Panic', $message);
            }
            // 动态配置覆盖
            if ($path=Config::exist(RUNTIME_DIR.'/global.config.php')) {
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
        if ($path=Config::exist(CONFIG_DIR.'/listener.json')) {
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
        // 检测 Composer vendor
        if (storage()->exist($vendor = APP_DIR.'/../vendor/autoload.php')){
            Autoloader::import($vendor);
        }
        // 注册模块目录
        $this->addModulesPath(SYSTEM_RESOURCE.'/modules');
        $this->addModulesPath(MODULES_DIR);
    }

    public static function getInstance()
    {
        $name=Config::get('app.application');
        if (is_null(self::$instance)) {
            self::$instance=new $name();
        }
        return self::$instance;
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
     * 载入模块
     *
     * @return void
     */
    protected function loadModules()
    {
        // 激活模块
        $moduleUse=self::getLiveModules();
        // 安装、启用使用的模块
        foreach ($moduleUse as $moduleTemp) {
            $root=self::getModulePath($moduleTemp);
            $config=self::getModuleConfig($moduleTemp);
            // 检查依赖
            if (isset($config['require'])) {
                $this->checkModuleRequire(__('module %s', $config['name']), $config['require']);
            }
            // 框架依赖
            if (isset($config['suda']) && !static::versionCompire($config['suda'], SUDA_VERSION)) {
                suda_panic('ApplicationException', __('module %s require suda version %s and now is %s', $moduleTemp, $config['suda'], SUDA_VERSION));
            }
            // 检测 Composer vendor
            if (storage()->exist($vendor = $root.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php')){
                Autoloader::import($vendor);
            }
            // 加载库路经
            foreach ($config['import']['share'] as $namespace=>$path) {
                if (Storage::isDir($dirPath=$root.DIRECTORY_SEPARATOR.$path)) {
                    Autoloader::addIncludePath($dirPath, $namespace);
                } elseif (Storage::isFile($importPath=$root.DIRECTORY_SEPARATOR.$path)) {
                    Autoloader::import($importPath);
                }
            }
            // 加载监听器
            if ($listenerPath=Config::exist($root.'/resource/config/listener.json')) {
                Hook::loadConfig($listenerPath);
                Hook::exec('loadModule:'.self::getModuleName($moduleTemp));
            }
            // 自动安装
            if (conf('auto-install', true)) {
                Hook::listen('Application:init', function () use ($moduleTemp) {
                    self::installModule($moduleTemp);
                });
            }
            // 设置语言包库
            Locale::path($root.'/resource/locales/');
        }
        Hook::exec('loadModule');
    }
    
    /**
     * 检查模块依赖
     *
     * @param string $name
     * @param array $requires
     * @return void
     */
    public function checkModuleRequire(string $name, array $requires)
    {
        foreach ($requires as $module => $version) {
            $require=$this->getModuleConfig($module);
            if ($this->checkModuleExist($module) && isset($require['version'])) {
                if (!empty($version)) {
                    if (!static::versionCompire($version, $require['version'])) {
                        suda_panic('ApplicationException', __('%s require module %s %s and now is %s', $name, $module, $version, $require['version']));
                    }
                }
            } else {
                suda_panic('ApplicationException', __('%s require module %s', $name, $module));
            }
        }
    }

    public function init()
    {
        // 读取目录，注册所有模块
        $this->registerModules();
        // 加载模块
        $this->loadModules();
        // 调整模板
        Manager::theme(conf('app.template', 'default'));
        // 初次运行初始化资源
        if (conf('app.init')) {
            init_resource();
        }
        Hook::exec('Application:init');
        Locale::path($this->path.'/resource/locales/');
        hook()->listen('Router:dispatch::before', [$this, 'onRequest']);
        hook()->listen('system:shutdown', [$this, 'onShutdown']);
        hook()->listen('system:uncaughtException', [$this,'uncaughtException']);
        hook()->listen('system:uncaughtError', [$this, 'uncaughtError']);
    }

    /**
     * 安装有自动安装功能的模块
     *
     * @param string $module
     * @return void
     */
    public function installModule(string $module)
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
     * 获取所有的模块
     *
     * @return array
     */
    public function getModules():array
    {
        return array_values($this->moduleDirName);
    }

    /**
     * 获取所有模块的目录
     *
     * @return array
     */
    public function getModuleDirs():array
    {
        return array_keys($this->moduleDirName);
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
     * 获取模块的配置信息
     * 
     * @example
     * 
     * 获取模块信息 (`module.json` 文件的内容)
     * 
     * ```php
     * app()->getModuleConfig(模块名);
     * ```
     * 
     * 获取配置信息（`module/resource/config/文件名.json` 文件的内容）
     * 
     * ```php
     * app()->getModuleConfig(模块名,文件名);
     * ```
     * 
     * @param string $module
     * @param string|null $configName
     * @return array|null
     */
    public function getModuleConfig(string $module, ?string $configName=null):?array
    {
        if (is_null($configName)) {
            return $this->moduleConfigs[self::getModuleFullName($module)]??[];
        }
        if ($path = self::getModuleConfigPath($module, $configName)) {
            return Config::loadConfig($path);
        }
        return null;
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
        if ($path = Config::exist(CONFIG_DIR .'/'.$configName)) {
            return Config::loadConfig($path);
        }
        return null;
    }

    /**
     * 获取模块 resouce 目录路径
     *
     * @param string $module
     * @return string
     */
    public function getModuleResourcePath(string $module):string
    {
        return self::getModulePath($module).'/resource';
    }
    
    /**
     * 获取模块 resource/config 路径
     *
     * @param string $module
     * @param string $name
     * @return string|null
     */
    public function getModuleConfigPath(string $module, string $name):?string
    {
        return  Config::exist(self::getModulePath($module).'/resource/config/'.$name)?:null;
    }

    /**
     * 获取模块网页前缀
     *
     * @param string $module
     * @return array
     */
    public function getModulePrefix(string $module)
    {
        $prefix=conf('module-prefix.'.$module, null);
        if (is_null($prefix)) {
            $prefix=$this->moduleConfigs[self::getModuleFullName($module)]['prefix']??null;
        }
        return $prefix;
    }

 
    /**
     * 检查模块是否存在
     *
     * @param string $name
     * @return boolean
     */
    public function checkModuleExist(string $name):bool
    {
        return $this->getModuleDir($name)!=null;
    }

    /**
     * 获取激活的模块
     *
     * @return array
     */
    public function getLiveModules()
    {
        if ($this->moduleLive) {
            return $this->moduleLive;
        }
        if (file_exists($path=RUNTIME_DIR.'/modules.config.php')) {
            $modules=include $path;
        } else {
            $modules=conf('app.modules', self::getModules());
        }
        $exclude=defined('DISABLE_MODULES')?explode(',', trim(DISABLE_MODULES, ',')):[];
        foreach ($exclude as $index=>$name) {
            $exclude[$index]=$this->getModuleFullName($name);
        }
        foreach ($modules as $index => $name) {
            $fullname=$this->getModuleFullName($name);
            // 剔除模块名
            if (!self::checkModuleExist($name) || in_array($fullname, $exclude)) {
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
        if ($this->routeReachable) {
            return $this->routeReachable;
        }
        if (file_exists($path=RUNTIME_DIR.'/reachable.config.php')) {
            $modules=include $path;
        } else {
            $modules=conf('app.reachable', self::getLiveModules());
        }
        $exclude=defined('UNREACHABLE_MODULES')?explode(',', trim(UNREACHABLE_MODULES, ',')):[];
        foreach ($exclude as $index=>$name) {
            $exclude[$index]=$this->getModuleFullName($name);
        }
        foreach ($modules as $index => $name) {
            $fullname=$this->getModuleFullName($name);
            // 剔除模块名
            if (!self::checkModuleExist($name) || in_array($fullname, $exclude)) {
                unset($modules[$index]);
            } else {
                $modules[$index]=$fullname;
            }
        }
        sort($modules);
        debug()->trace('reachable modules', json_encode($modules));
        return $this->routeReachable=$modules;
    }
    
    public function isModuleReachable(string $name)
    {
        return in_array($this->getModuleFullName($name), $this->getReachableModules());
    }

    /**
     * 激活运行的模块
     *
     * @param string $module
     * @return void
     */
    public function activeModule(string $module)
    {
        Hook::exec('Application:active', [$module]);
        debug()->trace(__('active module %s', $module));
        $this->activeModule=$module;
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
        foreach ($module_config['import']['src'] as $namespace=>$path) {
            if (Storage::isDir($srcPath=$root.DIRECTORY_SEPARATOR.$path)) {
                Autoloader::addIncludePath($srcPath, $namespace);
            } elseif (Storage::isFile($importPath=$root.DIRECTORY_SEPARATOR.$path)) {
                Autoloader::import($importPath);
            }
        }
        Config::set('module',$module_config);
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
     * 获取模块名，不包含版本号
     *
     * @param string $name 不完整模块名
     * @return string
     */
    public function getModuleName(string $name)
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
     * @return string
     */
    public function getModuleFullName(string $name)
    {
        // 存在缓存则返回缓存
        if (isset($this->moduleNameCache[$name])) {
            return $this->moduleNameCache[$name];
        }
        preg_match('/^(?:([a-zA-Z0-9_\-.]+)\/)?([a-zA-Z0-9_\-.]+)(?::(.+))?$/', $name, $matchname);
        $preg='/^'.(isset($matchname[1])&&$matchname[1]? preg_quote($matchname[1]).'\/':'(\w+\/)?') // 限制域
            .preg_quote($matchname[2]) // 名称
            .(isset($matchname[3])&&$matchname[3]?':'.preg_quote($matchname[3]):'(:.+)?').'$/'; // 版本号
        $targets=[];
        // debug()->debug($matchname, $preg);
        // 匹配模块名，查找符合格式的模块
        if (is_array($this->moduleConfigs)) {
            foreach ($this->moduleConfigs as $module_name=>$module_config) {
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
        }
        // 排序版本
        uksort($targets, 'version_compare');
        return count($targets)>0?array_pop($targets):$name;
    }

    /**
     * 获取模块所在的文件夹名
     *
     * @param string $name
     * @return string|null
     */
    public function getModuleDir(string $name):?string
    {
        $name=self::getModuleFullName($name);
        if (isset($this->moduleConfigs[$name])) {
            return $this->moduleConfigs[$name]['directory'];
        }
        return null;
    }

    /**
     * 根据模块目录名转换成模块名
     *
     * @param string $dirname
     * @return string
     */
    public function moduleName(string $dirname)
    {
        return $this->moduleDirName[$dirname]?:$name;
    }

    /**
     * 注册所有模块信息
     *
     * @return void
     */
    private function registerModules()
    {
        foreach ($this->modulesPath as $path) {
            $dirs=Storage::readDirs($path);
            foreach ($dirs as $dir) {
                self::registerModule($path.'/'.$dir);
            }
            $zips = Storage::readDirFiles($path,false,'/(\.zip|\.module|\.s?mod)$/');

            foreach ($zips as $zip) {
                $zipDir = RUNTIME_DIR.'/modules/'.basename($zip);
                if (conf('debug') || !Storage::isDir($zipDir)) {
                    ZipHelper::unzip($zip,$zipDir,true);
                    debug()->info(__('extract %s to %s',$zip,$zipDir));
                }
                self::registerModule($zipDir);
            }
        }
    }

    public function registerModule(string $path, ?string $config =null)
    {
        $config = is_null($config)?$path.'/module.json':$config;
        if (Storage::exist($path) && $config = Config::exist($config)) {
            $dir=basename($path);
            debug()->trace(__('load module config %s', $config));
            $configData=Config::loadConfig($config);
            $name=$configData['name'] ?? $dir;
            $version =  $configData['version'] ?? '';
            $configData['directory']=$dir;
            $configData['path']=$path;
            // 注册默认自动加载
            $configData['import']=array_merge([
                'share'=>[''=>'share/'],
                'src'=>[''=>'src/']
            ], $configData['import']??[]);
            $runtime = RUNTIME_DIR .'/module/'. $name . '/' . $version;
            $runtimeConfig = Config::loadConfig($runtime.'/module.config.php');
            if (is_array($runtimeConfig)) {
                $configData = array_merge($configData, $runtimeConfig);
            }
            $name.=empty($version)?'':':'.$version;
            $this->moduleConfigs[$name]=$configData;
            $this->moduleDirName[$dir]=$name;
            // 注册资源
            Manager::registerTemplateSource($name);
        }
    }

    public function getModulesInfo()
    {
        return $this->moduleConfigs;
    }

    public function getModulePath(string $module)
    {
        $name=self::getModuleFullName($module);
        if (isset($this->moduleConfigs[$name])) {
            return $this->moduleConfigs[$name]['path'];
        }
        return false;
    }

    private function configDBify()
    {
        if (file_exists($path=RUNTIME_DIR.'/database.config.php')) {
            $config=include $path;
            Config::set('database', $config);
        }
    }

    /**
     * 比较版本
     *
     * @param string $version 比较用的版本，包含比较符号
     * @param string $compire 对比的版本
     * @return void
     */
    protected static function versionCompire(string $version, string $compire)
    {
        $oparetor=['lt','<=','le','gt','>=','ge','==','=','eq','!=','<>','ne','<','>'];
        $preg=implode('|', $oparetor);
        if (preg_match('/^('.$preg.')(.+)$/i', $version, $match)) {
            list($s, $op, $ver)=$match;
            return  version_compare($compire, $ver, strtolower($op));
        }
        return version_compare($compire, $version, '>=');
    }

    /**
     * 根据函数调用栈判断调用时所属模块
     *
     * @param integer $deep
     * @return string|null
     */
    public static function getThisModule(int $deep=0):?string 
    {
        $debug=debug_backtrace();
        $info=$debug[$deep];
        while (!isset($info['file'])) {
            $deep++;
            $info=$debug[$deep];
        }
        return self::getFileModule($info['file']);
    }

    /**
     * 根据文件名判断所属模块
     *
     * @param string $file
     * @return string|null
     */
    public static function getFileModule(string $file):?string
    {
        $modules=app()->getModules();
        foreach ($modules as $module) {
            $config=app()->getModuleConfig($module);
            $modulePath=storage()->path($config['path']);
            if ($modulePath == substr($file, 0, strlen($modulePath))) {
                return $module;
            }
        }
        return null;
    }
}
