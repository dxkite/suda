<?php
namespace suda\application;

use suda\orm\DataSource;
use suda\framework\Config;
use suda\framework\Context;
use suda\framework\Request;
use suda\application\Module;
use suda\framework\Response;
use suda\application\Resource;
use suda\application\ModuleBag;
use suda\framework\loader\Loader;
use suda\framework\runnable\Runnable;
use suda\application\ApplicationContext;
use suda\application\template\ModuleTemplate;
use suda\application\loader\ApplicationLoader;
use suda\framework\arrayobject\ArrayDotAccess;
use suda\framework\http\Request as HttpRequest;
use suda\application\processor\FileRequestProcessor;
use suda\application\processor\TemplateRequestProcessor;

/**
 * 应用程序
 */
class Application extends ApplicationContext
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
     * @param array $manifast
     * @param \suda\framework\http\Request $request
     * @param \suda\framework\loader\Loader $loader
     */
    public function __construct(string $path, array $manifast, HttpRequest $request, Loader $loader)
    {
        parent::__construct($path, $manifast, $request, $loader);
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
     * 运行程序
     *
     * @return void
     */
    public function run()
    {
        $appLoader = new ApplicationLoader($this);
        $this->debug->time('loading application');
        $appLoader->load();
        $this->event->exec('application:load-config', [ $this->config ,$this]);
        $this->debug->timeEnd('loading application');
        $this->debug->time('loading datasource');
        $appLoader->loadDataSource();
        $this->event->exec('application:load-environment', [ $this->config ,$this]);
        $this->debug->timeEnd('loading datasource');
        $this->debug->time('loading route');
        $appLoader->loadRoute();
        $this->event->exec('application:load-route', [$this->route , $this]);
        $this->debug->timeEnd('loading route');
        $this->debug->time('match route');
        $result = $this->route->match($this->request());
        $this->debug->timeEnd('match route');
        if ($result !== null) {
            $this->event->exec('application:route:match::after', [$result, $this->request]);
        }
        $this->debug->time('sending response');
        try {
            $response = $this->route->run($this->request(), $this->response, $result);
            if (!$response->isSended()) {
                $response->sendContent();
            }
            $this->debug->info('resposned with code '. $response->getStatus());
        } catch (\Throwable $e) {
            $this->debug->uncaughtException($e);
        }
        $this->debug->timeEnd('sending response');
        $this->debug->info('system shutdown');
    }

    /**
     * 请求处理
     *
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    public function onRequest(Request $request, Response $response)
    {
        $route = $request->getAttribute('route-config') ?? [];
        $runnable = null;
        if (\array_key_exists('class', $route)) {
            $runnable = $this->className($route['class']).'->onRequest';
        } elseif (\array_key_exists('source', $route)) {
            $request->setAttribute('source', $route['source']);
            $runnable = FileRequestProcessor::class.'->onRequest';
        } elseif (\array_key_exists('template', $route)) {
            $request->setAttribute('template', $route['template']);
            $runnable = TemplateRequestProcessor::class.'->onRequest';
        } else {
            throw new \Exception('request failed');
        }
        return (new Runnable($runnable))($this, $request, $response);
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
     * @param string $name
     * @param array $parameter
     * @param boolean $allowQuery
     * @param string|null $default
     * @return string|null
     */
    public function getUrl(string $name, array $parameter = [], bool $allowQuery = true, ?string $default = null):?string
    {
        return $this->route->create($this->getFullModuleSource($name, $default), $parameter, $allowQuery);
    }

    /**
     * 获取模板页面
     *
     * @param string $name
     * @param string|null $default
     * @return \suda\application\template\ModuleTemplate
     */
    public function getTemplate(string $name, ?string $default = null): ModuleTemplate
    {
        if ($default === null && $this->running) {
            $default = $this->running->getFullName();
        }
        return new ModuleTemplate($this->getFullModuleSource($name, $default), $this);
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
}
