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
use suda\application\exception\wrapper\ExceptionContentWrapper;

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
     * @param \suda\framework\loader\Loader $loader
     */
    public function __construct(string $path, array $manifast, Loader $loader)
    {
        parent::__construct($path, $manifast, $loader);
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
     * 准备运行环境
     *
     * @return void
     */
    public function prepare()
    {
        $appLoader = new ApplicationLoader($this);
        $this->debug->info('===============================');
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
        $this->debug->info('-------------------------------');
    }

    /**
     * 运行程序
     *
     * @return void
     */
    public function run(Request $request, Response $response)
    {
        $response->getWrapper()->register(ExceptionContentWrapper::class, [\Throwable::class]);
        $this->debug->info('{request-time} {remote-ip} {request-method} {request-uri} debug={debug}', [
            'remote-ip' => $request->getRemoteAddr(),
            'debug' => SUDA_DEBUG,
            'request-uri' => $request->getUrl(),
            'request-method' => $request->getMethod(),
            'request-time' => date('Y-m-d H:i:s', \constant('SUDA_START_TIME')),
        ]);
        $this->debug->time('match route');
        $result = $this->route->match($request);
        $this->debug->timeEnd('match route');
        if ($result !== null) {
            $this->event->exec('application:route:match::after', [$result, $request]);
        }
        $this->debug->time('sending response');
        try {
            $response = $this->route->run($request, $response, $result);
            if (!$response->isSended()) {
                $response->sendContent();
            }
            $this->debug->info('resposned with code '. $response->getStatus());
        } catch (\Throwable $e) {
            $this->debug->uncaughtException($e);
            $response->sendContent($e);
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
     * @param \suda\framework\Request $request
     * @param string $name
     * @param array $parameter
     * @param boolean $allowQuery
     * @param string|null $default
     * @return string|null
     */
    public function getUrl(Request $request, string $name, array $parameter = [], bool $allowQuery = true, ?string $default = null):?string
    {
        $url = $this->route->create($this->getFullModuleSource($name, $default), $parameter, $allowQuery);
        return $this->getUrlIndex($request).'/'.ltrim($url, '/');
    }

    /**
     * 获取URL索引
     *
     * @param \suda\framework\Request $request
     * @return string
     */
    protected function getUrlIndex(Request $request):string
    {
        $indexs = $this->conf('indexs') ?? [ 'index.php' ];
        $index = ltrim($request->getIndex(), '/');
        if (!\in_array($index, $indexs)) {
            return $index;
        }
        return '';
    }

    /**
     * 获取模板页面
     *
     * @param string $name
     * @param \suda\framework\Request $request
     * @param string|null $default
     * @return \suda\application\template\ModuleTemplate
     */
    public function getTemplate(string $name, Request $request, ?string $default = null): ModuleTemplate
    {
        if ($default === null && $this->running) {
            $default = $this->running->getFullName();
        }
        return new ModuleTemplate($this->getFullModuleSource($name, $default), $this, $request, $default);
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
