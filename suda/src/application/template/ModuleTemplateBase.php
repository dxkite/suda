<?php
namespace suda\application\template;

use suda\framework\Config;
use suda\framework\Request;
use suda\application\Resource;
use suda\application\Application;
use suda\framework\filesystem\FileSystem;
use suda\application\template\TemplateUtil;
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
     * 路由组
     *
     * @var string|null
     */
    protected $group = null;

    /**
     * 安全URI路径
     *
     * @var string
     */
    protected $uriName = 'application';

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

    public function __construct(string $name, Application $application, Request $request, ?string $defaultModule = null)
    {
        $this->application = $application;
        $this->request = $request;
        list($this->module, $this->group, $this->name) = $application->parseRouteName($name, $defaultModule, 'default');
        $this->group = $request->getAttribute('group', $this->group);
        $this->config = $this->getModuleConfig($this->module);
        $this->uriName = TemplateUtil::getSafeUriName($this->application, $this->module);
        $this->value = [];
    }

    protected function getModuleConfig(?string $module)
    {
        return TemplateUtil::getConfig($this->application, $module);
    }

    protected function createCompiler():Compiler
    {
        $compiler = new ModuleTemplateCompiler;
        return $compiler;
    }

    protected function getModuleStaticPath(?string $module)
    {
        $name = $this->getModuleConfig($module)['static'];
        return $this->getResource($module)->getResourcePath($this->getTemplatePath().'/'.$name) ?? '';
    }
 

    protected function getModuleStaticOutpath(?string $module)
    {
        $config = $this->getModuleConfig($module);
        $path = $config['assets-path'].'/'. $this->getModuleUriName($module) .'/'.$config['static'];
        FileSystem::make($path);
        return $path;
    }

    protected function getModuleUriName(?string $module)
    {
        $config = $this->getModuleConfig($module);
        return $config['uri-name'];
    }

    protected function getResource(?string $module): Resource
    {
        return TemplateUtil::getResource($this->application, $module ?? $this->module);
    }

    protected function getTemplatePath()
    {
        return TemplateUtil::getTemplatePath($this->application);
    }

    protected function getStaticModulePrefix(?string $module = null)
    {
        if ($module === null) {
            $module = $this->module;
        }
        $this->prepareStaticModuleSource($module);
        $config = TemplateUtil::getConfig($this->application, $module);
        return $this->getModuleStaticAssetRoot($module) .'/'.$this->getModuleUriName($module). '/'.$config['static'];
    }

    protected function getModulePrefix(?string $module = null)
    {
        if ($module === null) {
            $module = $this->module;
        }
        return $this->getModuleAssetRoot($module) .'/'.$this->getModuleUriName($module);
    }

    protected function getModuleAssetRoot(?string $module)
    {
        return TemplateUtil::getRequestAsset($this->application, $this->request, $module);
    }

    protected function getModuleStaticAssetRoot(?string $module)
    {
        return TemplateUtil::getStaticRequestAsset($this->application, $this->request, $module);
    }

    protected function prepareStaticModuleSource(?string $module)
    {
        $static = $this->getModuleStaticPath($module);
        if (SUDA_DEBUG && is_dir($static) && !\in_array($static, static::$copyedStaticPaths)) {
            $from = $static;
            $to = $this->getModuleStaticOutpath($module);
            $time = sprintf('copy template static source %s => %s ', $from, $to);
            $this->application->debug()->time($time);
            if (FileSystem::copyDir($from, $to)) {
                $this->application->debug()->timeEnd($time);
                static::$copyedStaticPaths[] = $static;
            } else {
                $this->application->debug()->warning('Failed: '.$time);
            }
        }
    }

    /**
     * Get 应用环境
     *
     * @return  Application
     */ 
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Get 请求信息
     *
     * @return  Request
     */ 
    public function getRequest()
    {
        return $this->request;
    }
}
