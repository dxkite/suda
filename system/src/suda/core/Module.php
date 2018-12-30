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
use suda\core\Storage;
use suda\tool\ZipHelper;
use suda\core\Autoloader;
use suda\template\Manager;

/**
 * 模块处理类
 *
 * 包含了框架的模块处理方式
 *
 */
class Module
{
    /**
     * 模块配置
     *
     * @var array
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
    
    /**
     * 加载模块
     *
     * @param string $module
     * @return void
     */
    public function loadModule(string $module)
    {
        $root = $this->getModulePath($module);
        $config = $this->getModuleConfig($module);
        // 检查依赖
        if (isset($config['require'])) {
            $this->checkModuleRequire(__('module $0', $config['name']), $config['require']);
        }
        // 框架依赖
        if (isset($config['suda']) && !static::versionCompire($config['suda'], SUDA_VERSION)) {
            suda_panic('ModuleException', __('module $0 require suda version $1 and now is $2', $module, $config['suda'], SUDA_VERSION));
        }
        // 检测 Composer vendor
        if (storage()->exist($vendor = $root.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php')) {
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
        if ($listenerPath=Config::resolve($root.'/resource/config/listener.json')) {
            $fullname = $this->getModuleFullName($module);
            Hook::loadConfig($listenerPath, $fullname);
            Hook::exec('suda:module:load:on::'.$fullname);
        }
        // 自动安装
        if (conf('auto-install', true)) {
            Hook::listen('suda:application:init', function () use ($module) {
                $this->installModule($module);
            });
        }
        // 设置语言包库
        Locale::path($root.'/resource/locales/');
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
            if ($require = $this->getModuleConfig($module)) {
                if (!empty($version) && array_key_exists('version', $require)) {
                    if (!static::versionCompire($version, $require['version'])) {
                        suda_panic('ModuleException', __('$0 require module $1 $2 and now is $3', $name, $module, $version, $require['version']));
                    }
                }
            } else {
                suda_panic('ModuleException', __('$0 require module $1', $name, $module));
            }
        }
    }

    /**
     * 比较版本
     *
     * @param string $version 比较用的版本，包含比较符号
     * @param string $compire 对比的版本
     * @return int
     */
    protected static function versionCompire(string $version, string $compire)
    {
        if (preg_match('/^(<=?|>=?|<>|!=)(.+)$/i', $version, $match)) {
            list($s, $op, $ver)=$match;
            return  version_compare($compire, $ver, $op);
        }
        return version_compare($compire, $version, '>=');
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
            return $this->moduleConfigs[$this->getModuleFullName($module)]??[];
        }
        if ($path = $this->getModuleConfigPath($module, $configName)) {
            return Config::loadConfig($path, $module);
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
        return $this->getModulePath($module).'/resource';
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
        return  Config::resolve($this->getModulePath($module).'/resource/config/'.$name)?:null;
    }

    /**
     * 获取模块URL前缀
     *
     * @param string $module
     * @return array|string|null
     */
    public function getModulePrefix(string $module, string $group=null)
    {
        $prefixs=conf('router-prefix.'.$module, null);
        if (is_null($prefixs)) {
            $config = $this->getModuleConfig($module);
            if (array_key_exists('prefix', $config)) {
                $prefixs = $config['prefix'];
            }
        }
        if (is_array($prefixs)) {
            if (is_null($group)) {
                return $prefixs;
            } else {
                return $prefixs[$group] ?? '';
            }
        } elseif (is_string($prefixs)) {
            return $prefixs;
        }
        return null;
    }

 
    /**
     * 检查模块是否存在
     *
     * @param string $name
     * @return boolean
     */
    public function checkModuleExist(string $name):bool
    {
        $name=$this->getModuleFullName($name);
        return array_key_exists($name, $this->moduleConfigs);
    }

    /**
     * 获取模块名，不包含版本号
     *
     * @param string $name 不完整模块名
     * @return string
     */
    public function getModuleName(string $name)
    {
        $name=$this->getModuleFullName($name);
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
        // name = [namespace/]name[:version]
        // - 存在缓存则返回缓存
        if (array_key_exists($name, $this->moduleNameCache)) {
            return $this->moduleNameCache[$name];
        }
        // - 查找名称相同的系统
        // - 如果有命名空间则筛选命名空间
        // - 如果有版本则赛选版本
        // - 如果无版本则按版本大小排序，筛选最终版本
        preg_match('/^(?:('.System::NAME_MATCH.')\/)?('.System::NAME_MATCH.')(?::(.+))?$/', $name, $matchname);
        $preg='/^'.(isset($matchname[1])&&$matchname[1]? preg_quote($matchname[1]).'\/':'('.System::NAME_MATCH.'+\/)?') // 限制域
            .preg_quote($matchname[2]) // 名称
            .(isset($matchname[3])&&$matchname[3]?':'.preg_quote($matchname[3]):'(:.+)?').'$/'; // 版本号
        $targets=[];
        // 匹配模块名，查找符合格式的模块
        if (is_array($this->moduleConfigs)) {
            foreach ($this->moduleConfigs as $module_name=>$module_config) {
                // 匹配到模块名
                if (preg_match($preg, $module_name)) {
                    preg_match('/^(?:('.System::NAME_MATCH.')\/)?('.System::NAME_MATCH.')(?::(.+))?$/', $module_name, $matchname);
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
        // 选取版本号高的版本
        $fullname = count($targets)?array_pop($targets):$name;
        // 缓存
        $this->moduleNameCache[$name] = $fullname;
        return $fullname;
    }

    /**
     * 获取模块所在的文件夹名
     *
     * @param string $name
     * @return string|null
     */
    public function getModuleDir(string $name):?string
    {
        $name=$this->getModuleFullName($name);
        if (array_key_exists($name, $this->moduleConfigs)) {
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
     * 注册模块
     *
     * @param string $path
     * @param string|null|array $config
     * @return boolean
     */
    public function registerModule(string $modulePath, $config = null):bool
    {
        // 文件或者文件夹
        if (Storage::isDir($modulePath)) {
            $path = $modulePath;
        } else {
            $path = RUNTIME_DIR.'/modules/'. pathinfo($modulePath, PATHINFO_FILENAME) .'-'.substr(md5_file($modulePath), 0, 8);
            if (conf('debug') || !Storage::isDir($path)) {
                ZipHelper::unzip($modulePath, $path);
                debug()->info(__('unzip $0 to $1', $modulePath, $path));
            }
        }
        // 自定义配置或使用标准配置
        $config = is_null($config) ? 'module.json': $config;
        $configData = [];

        if (is_string($config)) {
            if ($config = Config::resolve($path.'/'.$config)) {
                $configData = Config::loadConfig($config);
            } else {
                return false;
            }
        } elseif (is_array($config)) {
            $configData=$config;
        }

        if (Storage::exist($path)) {
            $dir=basename($path);
            $name=$configData['name'] ?? $dir;
            $version =  $configData['version'] ?? '';
            $configData['directory']=$dir;
            $configData['path']=$path;
            // 注册默认自动加载
            $configData['import']=array_merge([
                'share'=>[''=>'share/'],
                'src'=>[''=>'src/']
            ], $configData['import']??[]);
            // 运行时配置覆盖
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
            debug()->trace(__('register module $0 from $1', $name, $modulePath));
            return true;
        }
        return false;
    }

    public function getModulesInfo()
    {
        return $this->moduleConfigs;
    }

    /**
     * 获取模块地址
     *
     * @param string $module
     * @return boolean|string
     */
    public function getModulePath(string $module)
    {
        $name= $this->getModuleFullName($module);
        if (isset($this->moduleConfigs[$name])) {
            return $this->moduleConfigs[$name]['path'];
        }
        return false;
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
        while (array_key_exists('file', $info)) {
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
        debug()->info($modules);
        foreach ($modules as $module) {
            $config=app()->getModuleConfig($module);
            $modulePath=storage()->path($config['path']);
            $dir = substr($file, 0, strlen($modulePath));
            debug()->info($modulePath);
            debug()->info($dir);
            if ($modulePath === $dir) {
                $next = substr($file, strlen($modulePath), 1);
                $nextIsSp = $next === '/' || $next === '\\';
                if ($nextIsSp) {
                    return $module;
                }
            }
        }
        return null;
    }
}
