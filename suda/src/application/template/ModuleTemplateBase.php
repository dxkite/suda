<?php
namespace suda\application\template;

use suda\framework\Request;
use suda\application\Application;
use suda\framework\filesystem\FileSystem;
use suda\application\template\compiler\Compiler;
use suda\application\Resource as ApplicationResource;

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

    /**
     * ModuleTemplateBase constructor.
     * @param string $name 模板描述符
     * @param Application $application
     * @param Request $request
     * @param string|null $defaultModule
     */
    public function __construct(string $name, Application $application, Request $request, ?string $defaultModule = null)
    {
        parent::__construct('', []);
        $this->application = $application;
        $this->request = $request;
        list($this->module, $this->group, $this->name) = $application->parseRouteName($name, $defaultModule, 'default');
        $this->group = $request->getAttribute('group', $this->group);
        $this->config = $this->getModuleConfig($this->module);
        $this->uriName = TemplateUtil::getSafeUriName($this->application, $this->module);
        $this->value = [];
    }

    /**
     * @param string|null $module
     * @return mixed
     */
    protected function getModuleConfig(?string $module)
    {
        return TemplateUtil::getConfig($this->application, $module);
    }


    /**
     * @return Compiler
     */
    protected function createCompiler(): Compiler
    {
        $compiler = new ModuleTemplateCompiler;
        $compiler->init();
        $this->application->event()->exec(
            'application:template:compile::create',
            [$compiler, $this->config, $this->application]
        );
        return $compiler;
    }

    /**
     * @param string|null $module
     * @param string|null $name
     * @return string|null
     */
    protected function getModuleStaticPath(?string $module, ?string  $name = null)
    {
        $name = $name ?? $this->getModuleConfig($module)['static'];
        return $this->getResource($module)->getResourcePath($this->getTemplatePath().'/'.$name) ?? '';
    }


    /**
     * @param string|null $module
     * @param string|null $name
     * @return string
     */
    protected function getModuleStaticOutputPath(?string $module, ?string  $name = null)
    {
        $config = $this->getModuleConfig($module);
        $name = $name ?? $this->getModuleConfig($module)['static'];
        $path = $config['assets-path'].'/'. $this->getModuleUriName($module) .'/'.$name;
        FileSystem::make($path);
        return $path;
    }

    /**
     * @param string|null $module
     * @return mixed
     */
    protected function getModuleUriName(?string $module)
    {
        $config = $this->getModuleConfig($module);
        return $config['uri-name'];
    }

    /**
     * @param string|null $module
     * @return ApplicationResource
     */
    protected function getResource(?string $module): ApplicationResource
    {
        return TemplateUtil::getResource($this->application, $module ?? $this->module);
    }

    /**
     * @return string
     */
    protected function getTemplatePath()
    {
        return TemplateUtil::getTemplatePath($this->application);
    }

    /**
     * @param string|null $module
     * @param string|null $name
     * @return string
     */
    protected function getStaticModulePrefix(?string $module = null, ?string $name = null)
    {
        if ($module === null) {
            $module = $this->module;
        }
        $this->prepareStaticModuleSource($module, $name);
        $config = TemplateUtil::getConfig($this->application, $module);
        $name = $name ?? $config['static'];
        return $this->getModuleStaticAssetRoot($module) .'/'.$this->getModuleUriName($module). '/'.$name;
    }

    /**
     * @param string|null $module
     * @return string
     */
    protected function getModulePrefix(?string $module = null)
    {
        if ($module === null) {
            $module = $this->module;
        }
        return $this->getModuleAssetRoot($module) .'/'.$this->getModuleUriName($module);
    }

    /**
     * @param string|null $module
     * @return string
     */
    protected function getModuleAssetRoot(?string $module)
    {
        return TemplateUtil::getRequestAsset($this->application, $this->request, $module);
    }

    /**
     * @param string|null $module
     * @return string
     */
    protected function getModuleStaticAssetRoot(?string $module)
    {
        return TemplateUtil::getStaticRequestAsset($this->application, $this->request, $module);
    }

    /**
     * @param string|null $module
     * @param string|null $name
     */
    protected function prepareStaticModuleSource(?string $module, ?string  $name = null)
    {
        $copySource = $this->application->conf('copy-static-source', SUDA_DEBUG);
        if ($copySource) {
            $static = $this->getModuleStaticPath($module, $name);
            $staticCopyed = is_dir($static) && in_array($static, static::$copiedStaticPaths);
            if ($staticCopyed === false) {
                $from = $static;
                $to = $this->getModuleStaticOutputPath($module, $name);
                $time = sprintf('copy template static source %s => %s ', $from, $to);
                $this->application->debug()->time($time);
                if (FileSystem::copyDir($from, $to)) {
                    $this->application->debug()->timeEnd($time);
                    static::$copiedStaticPaths[] = $static;
                } else {
                    $this->application->debug()->warning('Failed: '.$time);
                }
            }
        }
    }

    /**
     * @return Application
     */
    public function getApplication(): Application
    {
        return $this->application;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }
}
