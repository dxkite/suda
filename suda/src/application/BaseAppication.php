<?php
namespace suda\application;

use suda\framework\Request;
use suda\application\Module;
use suda\application\Resource;
use suda\application\ModuleBag;
use suda\application\LanguageBag;
use suda\framework\loader\Loader;
use suda\application\ApplicationContext;
use suda\application\template\ModuleTemplate;



/**
 * 基础应用程序
 */
class BaseAppication extends ApplicationContext
{
    /**
     * 模块集合
     *
     * @var ModuleBag
     */
    protected $module;

    /**
     * 字符串包
     *
     * @var LanguageBag
     */
    protected $language;

    /**
     * 模块路径
     *
     * @var string[]
     */
    protected $modulePaths;

    /**
     * 运行的模块
     *
     * @var Module
     */
    protected $running;
    
    /**
     * 系统准备完成
     *
     * @var boolean
     */
    protected $isPrepared = false;

    /**
     * 创建应用
     *
     * @param string $path
     * @param array $manifast
     * @param \suda\framework\loader\Loader $loader
     */
    public function __construct(string $path, array $manifast, Loader $loader)
    {
        parent::__construct($path, $manifast, $loader);
        $this->module = new ModuleBag;
        $this->initProperty($manifast);
    }

    /**
     * 添加模块
     *
     * @param \suda\application\Module $module
     * @return void
     */
    public function add(Module $module)
    {
        $this->module->add($module);
    }

    /**
     * 查找模块
     *
     * @param string $name
     * @return \suda\application\Module|null
     */
    public function find(string $name):?Module
    {
        return $this->module->get($name);
    }

    /**
     * 初始化属性
     *
     * @param array $manifast
     * @return void
     */
    protected function initProperty(array $manifast)
    {
        if (\array_key_exists('module-paths', $manifast)) {
            $this->modulePaths = $manifast['module-paths'];
        } else {
            $this->modulePaths = [ Resource::getPathByRelativedPath('modules', $this->path) ];
        }
    }

    /**
     * Get 模块路径
     *
     * @return  string[]
     */
    public function getModulePaths()
    {
        return $this->modulePaths;
    }


    /**
     * 转换类名
     *
     * @param string $name
     * @return string
     */
    protected function className(string $name)
    {
        return str_replace(['.','/'], '\\', $name);
    }

    /**
     * 语言翻译
     *
     * @param string $message
     * @param mixed  ...$args
     * @return string
     */
    public function _(string $message, ...$args):string
    {
        return $this->language->interpolate($message, ...$args);
    }

    /**
     * 获取URL
     *
     * @param \suda\framework\Request $request
     * @param string $name
     * @param array $parameter
     * @param boolean $allowQuery
     * @param string|null $default
     * @return string|null
     */
    public function getUrl(Request $request, string $name, array $parameter = [], bool $allowQuery = true, ?string $default = null):?string
    {
        $url = $this->route->create($this->getFullModuleSource($name, $default), $parameter, $allowQuery);
        return $this->getUrlIndex($request).'/'.ltrim($url, '/');
    }

    /**
     * 获取URL索引
     *
     * @param \suda\framework\Request $request
     * @return string
     */
    protected function getUrlIndex(Request $request):string
    {
        $indexs = $this->conf('indexs') ?? [ 'index.php' ];
        $index = ltrim($request->getIndex(), '/');
        if (!\in_array($index, $indexs)) {
            return $index;
        }
        return '';
    }

    /**
     * 获取模板页面
     *
     * @param string $name
     * @param \suda\framework\Request $request
     * @param string|null $default
     * @return \suda\application\template\ModuleTemplate
     */
    public function getTemplate(string $name, Request $request, ?string $default = null): ModuleTemplate
    {
        if ($default === null && $this->running) {
            $default = $this->running->getFullName();
        }
        return new ModuleTemplate($this->getFullModuleSource($name, $default), $this, $request, $default);
    }

    /**
     * 获取模板下的资源名
     *
     * @param string $name
     * @param string|null $default
     * @return string
     */
    protected function getFullModuleSource(string $name, ?string $default = null):string
    {
        if (strpos($name, ':') > 0) {
            $dotpos = \strrpos($name, ':');
            $module = substr($name, 0, $dotpos);
            $name = substr($name, $dotpos + 1);
            if ($moduleObj = $this->find($module)) {
                return $moduleObj->getFullName().':'.$name;
            }
        }
        if ($default !== null && ($moduleObj = $this->find($default))) {
            return $moduleObj->getFullName().':'.$name;
        }
        return $name;
    }

    /**
     * Get 字符串包
     *
     * @return  LanguageBag
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set 字符串包
     *
     * @param  LanguageBag  $language  字符串包
     *
     * @return  self
     */
    public function setLanguage(LanguageBag $language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get 运行的模块
     *
     * @return  Module
     */
    public function getRunning()
    {
        return $this->running;
    }

    /**
     * Set 运行的模块
     *
     * @param  Module  $running  运行的模块
     *
     * @return  self
     */
    public function setRunning(Module $running)
    {
        $this->running = $running;

        return $this;
    }

    /**
     * Get 模块集合
     *
     * @return  ModuleBag
     */ 
    public function getModule():ModuleBag
    {
        return $this->module;
    }

    /**
     * Set 模块集合
     *
     * @param  ModuleBag  $module  模块集合
     *
     * @return  self
     */ 
    public function setModule(ModuleBag $module)
    {
        $this->module = $module;

        return $this;
    }
}
