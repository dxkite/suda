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
class ModuleTemplateBase extends CompilableTemplate
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
      
        $this->config =  $this->getModuleConfig($this->module);
        $this->value = [];
    }

    protected function getModuleConfig(?string $module) {
        $configPath = $this->getResource($module)->getConfigResourcePath($this->getTemplatePath().'/config');
        $config = [];
        if ($configPath !== null) {
            $config = Config::loadConfig($configPath) ?? [];
        }
        if (!\array_key_exists('assets-prefix', $config)) {
            $config['assets-prefix'] = rtrim(str_replace('\\', '/', dirname($this->request->getIndex())), '/').'/assets';
        }
        return $config;
    }

    protected function createCompiler():Compiler
    {
        $compiler = new ModuleTemplateCompiler;
        return $compiler;
    }

    protected function getModuleStaticPath(?string $module)
    {
        $name = $this->getModuleConfig($module)['static'] ?? 'static';
        return $this->getResource($module)->getResourcePath($this->getTemplatePath().'/'.$name) ?? '';
    }

    protected function getModuleStaticOutpath(?string $module)
    {
        $path = $this->getModuleConfig($module)['assets-public'] ?? \constant('SUDA_PUBLIC').'/assets/'. $this->getModuleStaticName($module);
        FileSystem::makes($path);
        return $path;
    }

    protected function getModuleStaticName(?string $module)
    {
        $config = $this->getModuleConfig($module);
        if (is_array($config) && array_key_exists('static-name', $config)) {
            return $config['static-name'];
        }
        $static = $this->getModuleStaticPath($module);
        if ($static) {
            return substr(md5($static), 0, 8);
        }
        return '#';
    }

    protected function getResource(?string $module): Resource
    {
        if ($module !== null && ($moduleObj = $this->application->find($module))) {
            return $moduleObj->getResource();
        }
        return $this->application->getResource();
    }

    protected function getTemplatePath()
    {
        return 'template/'.$this->application->getStyle();
    }

    protected function getStaticModulePrefix(?string $module = null)
    {
        if ($module === null) {
            $module = $this->module;
        }
        $this->prepareStaticModuleSource($module);
        return $this->getModuleAssetRoot($module) .'/'.$this->getModuleStaticName($module);
    }

  
    protected function getModuleAssetRoot(?string $module) {
        $config = $this->getModuleConfig($module);
        if (\array_key_exists('assets-prefix', $config)) {
            $prefix = $config['assets-prefix'] ;
        } elseif (defined('SUDA_ASSETS')) {
            $prefix = \constant('SUDA_ASSETS');
        } else {
            $prefix = '/assets';
        }
        return $prefix;
    }

    protected function prepareStaticModuleSource(?string $module)
    {
        $static = $this->getModuleStaticPath($module);
        if (is_dir($static) && !\in_array($static, static::$copyedStaticPaths)) {
            $from = $static;
            $to = $this->getModuleStaticOutpath($module);
            $time = sprintf('copy template static source %s => %s ', $from, $to);
            $this->application->debug()->time($time);
            if (FileSystem::copyDir($from, $to)){
                $this->application->debug()->timeEnd($time);
                static::$copyedStaticPaths[] = $static;
            }else{
                $this->application->debug()->warning('Failed: '.$time);
            }
        }
    }
}
