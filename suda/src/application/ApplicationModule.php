<?php
namespace suda\application;

use function array_key_exists;
use function sprintf;
use suda\framework\loader\Loader;
use suda\application\exception\ApplicationException;

/**
 * 模块化应用
 */
class ApplicationModule extends ApplicationContext
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
     * 创建应用
     *
     * @param string $path
     * @param array $manifest
     * @param Loader $loader
     * @param string|null $dataPath
     */
    public function __construct(string $path, array $manifest, Loader $loader, ?string $dataPath = null)
    {
        parent::__construct($path, $manifest, $loader, $dataPath);
        $this->module = new ModuleBag;
        $this->initProperty($manifest);
    }

    /**
     * 添加模块
     *
     * @param Module $module
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
     * @return Module|null
     */
    public function find(string $name):?Module
    {
        return $this->module->get($name);
    }

    /**
     * 获取模块
     *
     * @param string $name
     * @return Module
     */
    public function get(string $name): Module
    {
        if (($module = $this->find($name)) !== null) {
            return $module;
        }
        throw new ApplicationException(sprintf('module %s not exist', $name), ApplicationException::ERR_MODULE_NAME);
    }

    /**
     * 初始化属性
     *
     * @param array $manifest
     * @return void
     */
    protected function initProperty(array $manifest)
    {
        if (array_key_exists('module-paths', $manifest)) {
            $modulePaths = $manifest['module-paths'];
            foreach ($modulePaths as $name => $path) {
                $this->modulePaths[] = Resource::getPathByRelativePath($path, $this->path);
            }
        } else {
            $this->modulePaths = [ Resource::getPathByRelativePath('modules', $this->path) ];
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
     * 语言翻译
     *
     * @param string|null $message
     * @param mixed ...$args
     * @return string
     */
    public function _(?string $message, ...$args):string
    {
        return $this->language->interpolate($message, ...$args);
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
     * @param Module $running
     * @return $this
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
    public function getModules():ModuleBag
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

    /**
     * 解析资源名
     *
     * @param string $name
     * @param string|null $default
     * @param string|null $groupName
     * @return array
     */
    public function parseSourceName(string $name, ?string $default = null, ?string $groupName = null)
    {
        if (strpos($name, ':') !== false) {
            $dotpos = strrpos($name, ':');
            $module = substr($name, 0, $dotpos);
            $name = substr($name, $dotpos + 1);
            if (strlen($module) === 0) {
                $module = $default;
            }
        } else {
            $module = $default;
        }
        if ($module !== null && strpos($module, '@') !== false) {
            list($module, $groupName) = explode('@', $module, 2);
            $module = strlen($module) ? $module : $default;
        }
        return [$module, $groupName, $name];
    }

    /**
     * 获取模板下的资源名
     *
     * @param string $name
     * @param string|null $default
     * @return string
     */
    public function getModuleSourceName(string $name, ?string $default = null): string
    {
        if (strpos($name, ':') > 0) {
            list($module, $group, $name) = $this->parseSourceName($name, $default);
        } else {
            $module = $default;
        }
        if ($module !== null && ($moduleObj = $this->find($module))) {
            return $moduleObj->getFullName() . ':' . $name;
        }
        return $name;
    }
}
