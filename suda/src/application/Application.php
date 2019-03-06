<?php
namespace suda\application;

use suda\application\Module;
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

    
    public function __construct(string $path)
    {
        $this->module = new ModuleBag;
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
}
