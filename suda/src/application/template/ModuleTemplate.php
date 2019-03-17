<?php
namespace suda\application\template;

use suda\framework\Config;
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

    public function __construct(string $name, Application $application, ?string $defaultModule = '')
    {
        $this->application = $application;
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
        if (!\array_key_exists('assets-prefix', $this->config)){
            $this->config['assets-prefix'] = dirname($this->application->getRequest()->getIndex()).'/assets';
        }
    }

    protected function createCompiler():Compiler
    {
        $compiler = new ModuleTemplateCompiler;
        return $compiler;
    }

    public function getUrl($name = null, $values = null)
    {
        $defaultName = $this->application->request()->getAttribute('route');
        if (is_string($name)) {
            if (!is_array($values)) {
                $args = func_get_args();
                array_shift($args);
                $values = $args;
            }
            return $this->application->getUrl($name, $values ?? [], true, $this->module);
        } elseif (is_array($name) && \is_string($defaultName)) {
            return $this->application->getUrl($defaultName, $name, true, $this->module);
        } elseif (is_string($defaultName)) {
            return $this->application->getUrl($defaultName, $this->application->request()->get() ?? [], true, $this->module);
        }
        return '#'.$defaultName;
    }

    protected function getStaticPath()
    {
        $name = $this->config['static'] ?? 'static';
        return $this->getResource()->getResourcePath($this->getTemplatePath().'/'.$name);
    }

    protected function getStaticOutpath()
    {
        $path = $this->config['assets-public'] ?? \constant('SUDA_PUBLIC').'/assets/'. $this->getStaticName();
        FileSystem::makes($path);
        return $path;
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
        $included = new self($name, $this->application, $this->module);
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
}
