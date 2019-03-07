<?php
namespace suda\application;

use suda\framework\Context;
use suda\application\Module;
use suda\application\Resource;
use suda\application\ModuleBag;

/**
 * 应用程序
 */
class Application
{
    /**
     * 应用路径
     *
     * @var string
     */
    protected $path;

    /**
     * 模块集合
     *
     * @var ModuleBag
     */
    protected $module;

    /**
     * 时区
     *
     * @var string
     */
    protected $timezone = 'PRC';

    /**
     * 语言
     *
     * @var string
     */
    protected $locate = 'zh-cn';

    /**
     * 使用的样式
     *
     * @var string
     */
    protected $style = 'default';
    
    /**
     * 配置数组
     *
     * @var array
     */
    protected $manifast;

    /**
     * 模块路径
     *
     * @var string
     */
    protected $modulePaths;

    /**
     * 运行环境
     *
     * @var Context $context
     */
    protected $context;

    public function __construct(string $path, array $manifast)
    {
        $this->path = $path;
        $this->module = new ModuleBag;
        $this->manifast = $manifast;
        $this->initProperty($manifast);
    }

    public function add(Module $module)
    {
        $this->module->add($module);
    }

    /**
     * Get 使用的样式
     *
     * @return  string
     */
    public function getStyle()
    {
        return $this->style;
    }

    /**
     * Set 使用的样式
     *
     * @param  string  $style  使用的样式
     *
     * @return  self
     */
    public function setStyle(string $style)
    {
        $this->style = $style;

        return $this;
    }

    /**
     * Get 语言
     *
     * @return  string
     */
    public function getLocate()
    {
        return $this->locate;
    }

    /**
     * Set 语言
     *
     * @param  string  $locate  语言
     *
     * @return  self
     */
    public function setLocate(string $locate)
    {
        $this->locate = $locate;

        return $this;
    }

    /**
     * Get 时区
     *
     * @return  string
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * Set 时区
     *
     * @param  string  $timezone  时区
     *
     * @return  self
     */
    public function setTimezone(string $timezone)
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Get 配置数组
     *
     * @return  string
     */
    public function getManifast()
    {
        return $this->manifast;
    }

    protected function initProperty(array $manifast)
    {
        if (\array_key_exists('style', $manifast)) {
            $this->style = \strtolower($manifast['style']);
        }
        if (\array_key_exists('locate', $manifast)) {
            $this->locate = \strtolower($manifast['locate']);
        }
        if (\array_key_exists('timezone', $manifast)) {
            $this->timezone = \strtolower($manifast['timezone']);
        }
        if (\array_key_exists('module-paths', $manifast)) {
            $this->modulePaths = \strtolower($manifast['module-paths']);
        } else {
            $this->modulePaths = [ Resource::getPathByRelativedPath('modules', $this->path) ];
        }
    }

    /**
     * Get 模块路径
     *
     * @return  string
     */
    public function getModulePaths()
    {
        return $this->modulePaths;
    }

    /**
     * Get $context
     *
     * @return  Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Set $context
     *
     * @param  Context  $context  $context
     *
     * @return  self
     */
    public function setContext(Context $context)
    {
        $this->context = $context;

        return $this;
    }
}
