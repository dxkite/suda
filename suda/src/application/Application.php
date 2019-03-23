<?php
namespace suda\application;

use Throwable;
use suda\framework\Request;
use suda\framework\Response;
use suda\application\BaseAppication;
use suda\framework\runnable\Runnable;
use suda\application\loader\ModuleLoader;
use suda\application\loader\LanguageLoader;
use suda\application\loader\ApplicationLoader;
use suda\application\processor\FileRequestProcessor;
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
        try {
            $response->getWrapper()->register(ExceptionContentWrapper::class, [\Throwable::class]);
            $this->debug->info('{request-time} {remote-ip} {request-method} {request-uri} debug={debug}', [
                'remote-ip' => $request->getRemoteAddr(),
                'debug' => SUDA_DEBUG,
                'request-uri' => $request->getUrl(),
                'request-method' => $request->getMethod(),
                'request-time' => date('Y-m-d H:i:s', \constant('SUDA_START_TIME')),
            ]);
            if ($this->isPrepared === false) {
                $this->prepare();
                $this->isPrepared = true;
            }
            $this->debug->time('match route');
            $result = $this->route->match($request);
            $this->debug->timeEnd('match route');
            if ($result !== null) {
                $this->event->exec('application:route:match::after', [$result, $request]);
            }
            $this->debug->time('sending response');
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
        $module = $request->getAttribute('module');
        if ($module && ($running = $this->find($module))) {
            $moduleLoader = new ModuleLoader($this, $running);
            $moduleLoader->toRunning();
        }
        LanguageLoader::load($this);
        $route = $request->getAttribute('config') ?? [];
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
        $runnable = [ $this, 'onRequest'];
        $this->route->request($method, $name, $url, $runnable, $attributes);
    }
}
