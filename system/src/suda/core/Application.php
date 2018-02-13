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

/**
 * 应用处理类
 *
 * 包含了应用的各种处理方式
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


    private static $instance;

    private function __construct()
    {
        debug()->trace(__('application load %s', APP_DIR));
        $this->path=APP_DIR;
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
        $this->configDBify();
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
        $this->addModulesPath(SYSTEM_RESOURCE.'/modules');
        $this->addModulesPath(MODULES_DIR);
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance=new self();
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
                foreach ($config['require'] as $module => $version) {
                    $require=$this->getModuleConfig($module);
                    if ($this->checkModuleExist($module) && isset($require['version'])) {
                        if (!empty($version)) {
                            if (!static::versionCompire($version, $config['version'])) {
                                throw new ApplicationException(__('module %s require module %s %s and now is %s', $config['name'], $module, $version, $require['version']));
                            }
                        }
                    } else {
                        throw new ApplicationException(__('module %s require module %s', $config['name'], $module));
                    }
                }
            }
            // 框架依赖
            if (isset($config['suda']) && !static::versionCompire($config['suda'], SUDA_VERSION)) {
                throw new ApplicationException(__('module %s require suda version %s and now is %s', $moduleTemp, $config['suda'], SUDA_VERSION));
            }
            foreach ($config['import']['share'] as $namespace=>$path) {
                if (Storage::isDir($dirPath=$root.DIRECTORY_SEPARATOR.$path)) {
                    Autoloader::addIncludePath($dirPath, $namespace);
                } elseif (Storage::isFile($importPath=$root.DIRECTORY_SEPARATOR.$path)) {
                    Autoloader::import($importPath);
                }
            }
            // 自动安装
            if (conf('auto-install', true)) {
                Hook::listen('Application:init', function () use ($moduleTemp) {
                    self::installModule($moduleTemp);
                });
            }
            // 加载监听器
            if (Storage::exist($listener_path=$root.'/resource/config/listener.json')) {
                Hook::loadJson($listener_path);
            }
            // 设置语言包库
            Locale::path($root.'/resource/locales/');
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
     * 获取所有模块
     *
     * @return void
     */
    public function getModules()
    {
        return array_values($this->moduleDirName);
    }

    public function getModuleDirs()
    {
        return array_keys($this->moduleDirName);
    }

    
    public function getActiveModule()
    {
        return $this->activeModule;
    }

    public function getModuleConfig(string $module)
    {
        return $this->moduleConfigs[self::getModuleFullName($module)]??[];
    }

    public function getModulePrefix(string $module)
    {
        $prefix=conf('module-prefix.'.$module, null);
        if (is_null($prefix)) {
            $prefix=$this->moduleConfigs[self::getModuleFullName($module)]['prefix']??null;
        }
        return $prefix;
    }

    public function checkModuleExist(string $name)
    {
        return $this->getModuleDir($name)!=false;
    }

    public function getLiveModules()
    {
        if ($this->moduleLive) {
            return $this->moduleLive;
        }
        $modules=conf('app.modules', self::getModules());
        $exclude=defined('DISABLE_MODULES')?explode(',', trim(DISABLE_MODULES, ',')):[];
        foreach ($exclude as $index=>$name) {
            $exclude[$index]=$this->getModuleFullName($name);
        }
        // debug()->trace('modules', json_encode($modules));
        // debug()->trace('exclude', json_encode($exclude));
        foreach ($modules as $index => $name) {
            $fullname=$this->getModuleFullName($name);
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
        return $this->moduleLive=$modules;
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
        // 加载模块配置到 module命名空间
        if (Storage::exist($path=MODULE_CONFIG.'/config.json')) {
            Config::set('module', Json::loadFile($path));
        }
    }


    public function onRequest(Request $request)
    {
        return true;
    }
    
    public function onShutdown()
    {
        return true;
    }

    public function uncaughtException($e)
    {
        return false;
    }

    /**
     * 获取模块名，不包含版本号
     *
     * @param string $name 不完整模块名
     * @return void
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
     * @return void
     */
    public function getModuleFullName(string $name)
    {
        // 存在缓存则返回缓存
        if (isset($this->moduleNameCache[$name])) {
            return $this->moduleNameCache[$name];
        }
        preg_match('/^(?:([a-zA-Z0-9_-]+)\/)?([a-zA-Z0-9_-]+)(?::(.+))?$/', $name, $matchname);
        $preg='/^'.(isset($matchname[1])&&$matchname[1]? preg_quote($matchname[1]).'\/':'(\w+\/)?') // 限制域
            .preg_quote($matchname[2]) // 名称
            .(isset($matchname[3])&&$matchname[3]?':'.preg_quote($matchname[3]):'(:.+)?').'$/'; // 版本号
        $targets=[];
        // debug()->debug($matchname, $preg);
        // 匹配模块名，查找符合格式的模块
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
    public function getModuleDir(string $name)
    {
        $name=self::getModuleFullName($name);
        if (isset($this->moduleConfigs[$name])) {
            return $this->moduleConfigs[$name]['directory'];
        }
        return false;
    }

    /**
     * 根据模块目录名转换成模块名
     *
     * @param string $dirname
     * @return void
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
        }
    }

    public function registerModule(string $path)
    {
        if (Storage::exist($file=$path.'/module.json')) {
            $dir=basename($path);
            debug()->trace(__('load module config %s', $file));
            $json=Json::parseFile($file);
            $name=$json['name'] ?? $dir;
            $json['directory']=$dir;
            $json['path']=$path;
            // 注册默认自动加载
            $json['import']=array_merge([
                'share'=>[''=>'share/'],
                'src'=>[''=>'src/']
            ], $json['import']??[]);
            $name.=isset($json['version'])?':'.$json['version']:'';
            $this->moduleConfigs[$name]=$json;
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
}
