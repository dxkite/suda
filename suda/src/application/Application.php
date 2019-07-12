<?php

namespace suda\application;

use Exception;
use Throwable;
use ReflectionException;
use suda\framework\Request;
use suda\framework\Response;
use suda\framework\loader\Loader;
use suda\framework\route\MatchResult;
use suda\application\template\Template;
use suda\application\loader\ModuleLoader;
use suda\database\exception\SQLException;
use suda\application\template\RawTemplate;
use suda\application\loader\LanguageLoader;
use suda\application\wrapper\TemplateWrapper;
use suda\application\loader\ApplicationLoader;
use suda\application\processor\FileRequestProcessor;
use suda\framework\http\Request as RequestInterface;
use suda\application\wrapper\ExceptionContentWrapper;
use suda\application\exception\ConfigurationException;
use suda\framework\http\Response as ResponseInterface;
use suda\application\processor\TemplateAssetProccesser;
use suda\application\processor\TemplateRequestProcessor;

/**
 * 应用程序
 */
class Application extends ApplicationSource
{
    /**
     * @var DebugDumper
     */
    protected $dumper;

    /**
     * Application constructor.
     * @param string $path
     * @param array $manifest
     * @param Loader $loader
     * @param string|null $dataPath
     */
    public function __construct(string $path, array $manifest, Loader $loader, ?string $dataPath = null)
    {
        parent::__construct($path, $manifest, $loader, $dataPath);
        $this->event = new Event($this);
    }


    /**
     * 准备运行环境
     *
     * @return void
     * @throws SQLException
     * @throws ReflectionException
     */
    public function load()
    {
        $appLoader = new ApplicationLoader($this);
        $this->debug->info('===============================');
        $this->debug->time('loading application');
        $appLoader->load();
        $this->event->exec('application:load-config', [$this->config, $this]);
        $this->debug->timeEnd('loading application');
        $this->debug->time('loading data-source');
        $appLoader->loadDataSource();
        $this->debug->timeEnd('loading data-source');
        $this->event->exec('application:load-environment', [$this->config, $this]);
        $this->debug->time('loading route');
        $appLoader->loadRoute();
        $this->event->exec('application:load-route', [$this->route, $this]);
        $this->debug->timeEnd('loading route');
        $this->debug->info('-------------------------------');
    }

    /**
     * 准备环境
     *
     * @param Request $request
     * @param Response $response
     * @throws SQLException
     * @throws ReflectionException
     */
    protected function prepare(Request $request, Response $response)
    {
        $response->setHeader('x-powered-by', 'suda/' . SUDA_VERSION, true);
        $response->getWrapper()->register(ExceptionContentWrapper::class, [Throwable::class]);
        $response->getWrapper()->register(TemplateWrapper::class, [RawTemplate::class]);

        $this->dumper = new DebugDumper($this, $request, $response);
        $this->dumper->register();

        $this->debug->info('{request-time} {remote-ip} {request-method} {request-uri} debug={debug}', [
            'remote-ip' => $request->getRemoteAddr(),
            'debug' => SUDA_DEBUG,
            'request-uri' => $request->getUrl(),
            'request-method' => $request->getMethod(),
            'request-time' => date('Y-m-d H:i:s', constant('SUDA_START_TIME')),
        ]);

        $this->load();
    }

    /**
     * @param Throwable $throwable
     */
    public function dumpException($throwable)
    {
        $this->dumper->dumpThrowable($throwable);
    }

    /**
     * 运行程序
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        $appRequest = new Request($request);
        $appResponse = new Response($response);

        try {
            $this->prepare($appRequest, $appResponse);
            $this->debug->time('match route');
            $result = $this->route->match($appRequest);
            $this->debug->timeEnd('match route');
            if ($result !== null) {
                $this->event->exec('application:route:match::after', [$result, $appRequest]);
            }
            $this->debug->time('sending response');
            $appResponse = $this->createResponse($result, $appRequest, $appResponse);
            if (!$appResponse->isSend()) {
                $appResponse->end();
            }
            $this->debug->info('responded with code ' . $appResponse->getStatus());
            $this->debug->timeEnd('sending response');
        } catch (Throwable $e) {
            $this->debug->uncaughtException($e);
            $this->dumper->dumpThrowable($e);
            $appResponse->sendContent($e);
            $this->debug->timeEnd('sending response');
        }
        $this->debug->info('system shutdown');
    }

    /**
     * 添加请求
     *
     * @param array $method
     * @param string $name
     * @param string $url
     * @param array $attributes
     * @return void
     */
    public function request(array $method, string $name, string $url, array $attributes = [])
    {
        $route = $attributes['config'] ?? [];
        $runnable = null;
        if (array_key_exists('class', $route)) {
            $runnable = $this->className($route['class']) . '->onRequest';
        } elseif (array_key_exists('source', $route)) {
            $attributes['source'] = $route['source'];
            $runnable = FileRequestProcessor::class . '->onRequest';
        } elseif (array_key_exists('template', $route)) {
            $attributes['template'] = $route['template'];
            $runnable = TemplateRequestProcessor::class . '->onRequest';
        } elseif (array_key_exists('runnable', $route)) {
            $runnable = $route['runnable'];
        } else {
            throw new ConfigurationException('request config error', ConfigurationException::ERR_CONFIG_SET);
        }
        $this->route->request($method, $name, $url, $runnable, $attributes);
    }

    /**
     * 运行默认请求
     * @param Application $application
     * @param Request $request
     * @param Response $response
     * @return mixed|void
     * @throws Exception
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
     * @param MatchResult|null $result
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws Exception
     */
    protected function createResponse(?MatchResult $result, Request $request, Response $response)
    {
        if (SUDA_DEBUG) {
            $response->setHeader('x-route', $result === null ? 'default' : $result->getName());
        }
        if ($result === null) {
            $content = $this->defaultResponse($this, $request, $response);
        } else {
            $content = $this->runResult($result, $request, $response);
        }
        if ($content !== null && !$response->isSend()) {
            $response->setContent($content);
        }
        return $response;
    }

    /**
     * 运行结果
     *
     * @param MatchResult $result
     * @param Request $request
     * @param Response $response
     * @return mixed
     * @throws ReflectionException
     */
    protected function runResult(MatchResult $result, Request $request, Response $response)
    {
        $request
            ->setParameter($request->getParameter())
            ->mergeQueries($result->getParameter())
            ->setAttributes($result->getMatcher()->getAttribute());
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
     * 获取模板页面
     *
     * @param string $name
     * @param Request $request
     * @param string|null $default
     * @return Template
     */
    public function getTemplate(string $name, Request $request, ?string $default = null): Template
    {
        if ($default === null && $this->running !== null) {
            $default = $this->running->getFullName();
        } else {
            $default = $default ?? $request->getAttribute('module');
        }
        return new Template($this->getModuleSourceName($name, $default), $this, $request, $default);
    }
}
