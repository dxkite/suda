<?php
namespace suda\application;

use Throwable;
use suda\framework\Request;
use suda\framework\Response;
use suda\application\BaseAppication;
use suda\application\database\Table;
use suda\framework\route\MatchResult;
use suda\framework\runnable\Runnable;
use suda\application\loader\ModuleLoader;
use suda\application\loader\LanguageLoader;
use suda\application\template\ModuleTemplate;
use suda\application\loader\ApplicationLoader;
use suda\application\processor\FileRequestProcessor;
use suda\application\processor\TemplateAssetProccesser;
use suda\application\processor\TemplateRequestProcessor;
use suda\application\exception\wrapper\ExceptionContentWrapper;

/**
 * 应用程序
 */
class Application extends BaseAppication
{
    /**
     * 准备运行环境
     *
     * @return void
     */
    public function load()
    {
        $appLoader = new ApplicationLoader($this);
        $this->debug->info('===============================');
        $this->debug->time('loading application');
        $appLoader->load();
        $this->event->exec('application:load-config', [ $this->config ,$this]);
        $this->debug->timeEnd('loading application');
        $this->debug->time('loading datasource');
        $appLoader->loadDataSource();
        Table::load($this);
        $this->event->exec('application:load-environment', [ $this->config ,$this]);
        $this->debug->timeEnd('loading datasource');
        $this->debug->time('loading route');
        $appLoader->loadRoute();
        $this->event->exec('application:load-route', [$this->route , $this]);
        $this->debug->timeEnd('loading route');
        $this->debug->info('-------------------------------');
    }

    /**
     * 准备环境
     *
     * @param \suda\framework\Request $request
     * @param \suda\framework\Response $response
     * @return void
     */
    protected function prepare(Request $request, Response $response)
    {
        $response->setHeader('x-powered-by', 'nebula/'.SUDA_VERSION, true);
        $response->getWrapper()->register(ExceptionContentWrapper::class, [\Throwable::class]);
        $this->debug->info('{request-time} {remote-ip} {request-method} {request-uri} debug={debug}', [
            'remote-ip' => $request->getRemoteAddr(),
            'debug' => SUDA_DEBUG,
            'request-uri' => $request->getUrl(),
            'request-method' => $request->getMethod(),
            'request-time' => date('Y-m-d H:i:s', \constant('SUDA_START_TIME')),
        ]);
        if ($this->isPrepared === false) {
            $this->load();
            $this->isPrepared = true;
        }
    }

    /**
     * 运行程序
     *
     * @return void
     */
    public function run(Request $request, Response $response)
    {
        try {
            $this->prepare($request, $response);
            $this->debug->time('match route');
            $result = $this->route->match($request);
            $this->debug->timeEnd('match route');
            if ($result !== null) {
                $this->event->exec('application:route:match::after', [$result, $request]);
            }
            $this->debug->time('sending response');
            $response = $this->createResponse($result, $request, $response);
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
     * 添加请求
     *
     * @param array $method
     * @param string $name
     * @param string $url
     * @param \suda\framework\runnable\Runnable|\Closure|array|string $runnable
     * @param array $attributes
     * @return self
     */
    public function request(array $method, string $name, string $url, array $attributes = [])
    {
        $route = $attributes['config'] ?? [];
        $runnable = null;
        if (\array_key_exists('class', $route)) {
            $runnable = $this->className($route['class']).'->onRequest';
        } elseif (\array_key_exists('source', $route)) {
            $attributes['source'] = $route['source'];
            $runnable = FileRequestProcessor::class.'->onRequest';
        } elseif (\array_key_exists('template', $route)) {
            $attributes['template'] = $route['template'];
            $runnable = TemplateRequestProcessor::class.'->onRequest';
        } elseif (\array_key_exists('runnable', $route)) {
            $runnable = $route['runnable'];
        } else {
            throw new \Exception('request failed');
        }
        $this->route->request($method, $name, $url, $runnable, $attributes);
    }

    /**
     * 运行默认请求
     */
    protected function defaultResponse(Application $application, Request $request, Response $response)
    {
        if ((new TemplateAssetProccesser)->onRequest($application, $request, $response)) {
            return;
        }
        return $this->route->getDefaultRunnable()->run($request, $response);
    }

    /**
     * 运行请求
     */
    protected function createResponse(?MatchResult $result, Request $request, Response $response)
    {
        if (SUDA_DEBUG) {
            $response->setHeader('x-route', $result === null?'default':$result->getName());
        }
        if ($result === null) {
            $content = $this->defaultResponse($this, $request, $response);
        } else {
            $content = $this->runResult($result, $request, $response);
        }
        if ($content !== null && !$response->isSended()) {
            $response->setContent($content);
        }
        return $response;
    }

    /**
     * 运行结果
     *
     * @param MatchResult $result
     * @param \suda\framework\Request $request
     * @param \suda\framework\Response $response
     * @return mixed
     */
    protected function runResult(MatchResult $result, Request $request, Response $response)
    {
        $request->mergeQueries($result->getParameter())->setAttributes($result->getMatcher()->getAttribute());
        $request->setAttribute('result', $result);
        $module = $request->getAttribute('module');
        if ($module && ($running = $this->find($module))) {
            $moduleLoader = new ModuleLoader($this, $running);
            $moduleLoader->toRunning();
        }
        LanguageLoader::load($this);
        return ($result->getRunnable())($this, $request, $response);
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
    public function getUrl(Request $request, string $name, array $parameter = [], bool $allowQuery = true, ?string $default = null, ?string $group = null):?string
    {
        $url = $this->route->create($this->getRouteName($name, $default, $group), $parameter, $allowQuery);
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
        return new ModuleTemplate($this->getModuleSourceName($name, $default), $this, $request, $default);
    }

    /**
     * 获取模板下的资源名
     *
     * @param string $name
     * @param string|null $default
     * @return string
     */
    public function getModuleSourceName(string $name, ?string $default = null):string
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
     * 获取路由全名
     *
     * @param string $name
     * @param string|null $default
     * @return string
     */
    public function getRouteName(string $name, ?string $default = null, ?string $group = null):string
    {
        if (strpos($name, ':') > 0) {
            list($module, $group, $name) = $this->parseRouteName($name, $group);
            $prefixGroup = $group === null || $group === 'default' ? '': '@'. $group;
            if ($moduleObj = $this->find($module)) {
                return $moduleObj->getFullName().$prefixGroup.':'.$name;
            }
        }
        if ($default !== null && ($moduleObj = $this->find($default))) {
            $prefixGroup = $group === null || $group === 'default' ? '': '@'. $group;
            return $moduleObj->getFullName().$prefixGroup.':'.$name;
        }
        return $name;
    }

    /**
     * 拆分路由名
     *
     * @param string $name
     * @param string|null $groupName
     * @return array
     */
    protected function parseRouteName(string $name, ?string $groupName)
    {
        $dotpos = \strrpos($name, ':');
        $module = substr($name, 0, $dotpos);
        $name = substr($name, $dotpos + 1);
        if (strpos($module, '@')) {
            list($module, $groupName) = \explode('@', $module, 2);
        }
        return [$module, $groupName, $name];
    }
}
