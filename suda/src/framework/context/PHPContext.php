<?php
namespace suda\framework\context;

use suda\framework\Config;
use suda\framework\Debugger;
use suda\framework\http\Request;
use suda\framework\loader\Loader;

/**
 * PHP环境
 */
class PHPContext extends ServerContext
{
    /**
     * PHP自动加载
     *
     * @var \suda\framework\loader\Loader
     */
    protected $loader;
    
    /**
     * 全局配置
     *
     * @var \suda\framework\Config
     */
    protected $config;

    /**
     * PHP错误调试
     *
     * @var \suda\framework\Debugger
     */
    protected $debug;

    /**
     * 创建PHP环境
     *
     * @param \suda\framework\http\Request $request
     * @param \suda\framework\Config $config
     * @param \suda\framework\loader\Loader $loader
     */
    public function __construct(Request $request, Config $config, Loader $loader)
    {
        parent::__construct($request);
        $this->loader = $loader;
        $this->config = $config;
        $this->debug = Debugger::create($this)->register();
    }

    /**
     * 获取加载器
     *
     * @return \suda\framework\loader\Loader
     */
    public function loader():Loader
    {
        return $this->loader;
    }

    /**
     * 获取配置
     *
     * @return \suda\framework\Config
     */
    public function config():Config
    {
        return $this->config;
    }

    /**
     * 获取调试工具
     *
     * @return \suda\framework\Debugger
     */
    public function debug(): Debugger
    {
        return $this->debug;
    }

    /**
     * 获取配置信息
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function conf(string $name, $default = null)
    {
        return $this->config->get($name, $default);
    }

    /**
     * Get PHP自动加载
     *
     * @return  \suda\framework\loader\Loader
     */ 
    public function getLoader()
    {
        return $this->loader;
    }

    /**
     * Set PHP自动加载
     *
     * @param  \suda\framework\loader\Loader  $loader  PHP自动加载
     *
     * @return  self
     */ 
    public function setLoader(\suda\framework\loader\Loader $loader)
    {
        $this->loader = $loader;

        return $this;
    }

    /**
     * Get 全局配置
     *
     * @return  \suda\framework\Config
     */ 
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Set 全局配置
     *
     * @param  \suda\framework\Config  $config  全局配置
     *
     * @return  self
     */ 
    public function setConfig(\suda\framework\Config $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Get pHP错误调试
     *
     * @return  \suda\framework\Debugger
     */ 
    public function getDebug()
    {
        return $this->debug;
    }

    /**
     * Set PHP错误调试
     *
     * @param  \suda\framework\Debugger  $debug  PHP错误调试
     *
     * @return  self
     */ 
    public function setDebug(\suda\framework\Debugger $debug)
    {
        $this->debug = $debug;

        return $this;
    }
}
