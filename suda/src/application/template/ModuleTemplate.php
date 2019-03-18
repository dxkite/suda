<?php
namespace suda\application\template;

use suda\framework\Config;
use suda\framework\Request;
use suda\application\Resource;
use suda\application\Application;
use suda\framework\filesystem\FileSystem;
use suda\application\template\compiler\Compiler;
use suda\application\template\CompilableTemplate;
use suda\application\template\ModuleTemplateCompiler;

/**
 * 模块模板
 */
class ModuleTemplate extends CompilableTemplate
{
    /**
     * 模板模块
     *
     * @var string|null
     */
    protected $module = null;

    /**
     * 模板名
     *
     * @var string
     */
    protected $name;

    /**
     * 应用环境
     *
     * @var Application
     */
    protected $application;

    /**
     * 配置文件
     *
     * @var array
     */
    protected $config;

    /**
     * 请求信息
     *
     * @var Request
     */
    protected $request;

    public function __construct(string $name, Application $application, Request $request, ?string $defaultModule = '')
    {
        $this->application = $application;
        $this->request = $request;
        if (strpos($name, ':') > 0) {
            $dotpos = \strrpos($name, ':');
            $this->name = substr($name, $dotpos + 1);
            $this->module = substr($name, 0, $dotpos);
        } else {
            $this->name = $name;
            $this->module = $defaultModule;
        }
      
        $this->initConfig();
        $this->value = [];
    }

    protected function initConfig()
    {
        $config = $this->getResource()->getConfigResourcePath($this->getTemplatePath().'/config');
        if ($config !== null) {
            $this->config = Config::loadConfig($config) ?? [];
        }
        $this->config = [];
        if (!\array_key_exists('assets-prefix', $this->config)) {
            $this->config['assets-prefix'] = rtrim(str_replace('\\', '/', dirname($this->request->getIndex())), '/').'/assets';
        }
    }

    protected function createCompiler():Compiler
    {
        $compiler = new ModuleTemplateCompiler;
        return $compiler;
    }

    public function getUrl($name = null, $values = null)
    {
        $defaultName = $this->request->getAttribute('route');
        if (is_string($name)) {
            if (!is_array($values)) {
                $args = func_get_args();
                array_shift($args);
                $values = $args;
            }
            return $this->application->getUrl($this->request, $name, $values ?? [], true, $this->module);
        } elseif (is_array($name) && \is_string($defaultName)) {
            return $this->application->getUrl($this->request, $defaultName, $name, true, $this->module);
        } elseif (is_string($defaultName)) {
            return $this->application->getUrl($this->request, $defaultName, $this->request->get() ?? [], true, $this->module);
        }
        return '#'.$defaultName;
    }

    protected function getModuleStaticPath(string $module)
    {
        $name = $this->config['static'] ?? 'static';
        return $this->getResource()->getResourcePath($this->getTemplatePath().'/'.$name);
    }

    protected function getModuleStaticOutpath(string $module)
    {
        $path = $this->config['assets-public'] ?? \constant('SUDA_PUBLIC').'/assets/'. $this->getModuleStaticName($module);
        FileSystem::makes($path);
        return $path;
    }

    protected function getModuleStaticName(string $module)
    {
        return $this->config['static-name'] ?? substr(md5($this->staticPath), 0, 8);
    }


    protected function getSourcePath()
    {
        $subfix = $this->config['subfix'] ?? '.tpl.html';
        return $this->getResource()->getResourcePath($this->getTemplatePath().'/'.$this->name.$subfix);
    }

    protected function getPath()
    {
        $output = $this->config['output'] ?? \constant('SUDA_DATA').'/template/'. str_replace([':','/','\\'], '-', $this->module);
        FileSystem::makes($output);
        return $output .'/'. $this->name.'.php';
    }

    public function include(string $name)
    {
        $included = new self($name, $this->application, $this->request, $this->module);
        $included->parent = $this;
        echo $included->__toString();
    }

    protected function getResource(): Resource
    {
        if ($this->module !== null && $module = $this->application->find($this->module)) {
            return $module->getResource();
        }
        return $this->application->getResource();
    }

    protected function getTemplatePath()
    {
        return 'template/'.$this->application->getStyle();
    }

    protected function getStaticModulePrefix(string $module = null)
    {
        if ($module === null) {
            $module = $this->module;
        }
        $this->prepareStaticModuleSource($module);
        return $this->getModuleAssetRoot($module) .'/'.$this->getStaticName($module);
    }

    protected function getModuleAssetRoot(string $module) {
        if (\array_key_exists('assets-prefix', $this->config)) {
            $prefix = $this->config['assets-prefix'] ;
        } elseif (defined('SUDA_ASSETS')) {
            $prefix = \constant('SUDA_ASSETS');
        } else {
            $prefix = '/assets';
        }
        return $prefix;
    }

    protected function prepareStaticModuleSource(string $module)
    {
        if (is_dir($this->getModuleStaticPath($module)) && !\in_array($this->getModuleStaticPath($module), static::$copyedStaticPaths)) {
            $from = $this->getModuleStaticPath($module);
            $to = $this->getModuleStaticOutpath($module);
            $time = sprintf('copy module template static source %s => %s ', $from, $to);
            $this->application->debug()->time($time);
            if (FileSystem::copyDir($from, $to)){
                $this->application->debug()->timeEnd($time);
                static::$copyedStaticPaths[] = $this->getModuleStaticPath($module);
            }else{
                $this->application->debug()->warnnig('Failed: '.$time);
            }
        }
    }
}
