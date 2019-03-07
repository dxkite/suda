<?php
namespace suda\framework;

use suda\framework\Context;
use suda\framework\Request;
use suda\framework\http\Request as RawRequest;

/**
 * 服务提供者
 */
class Service
{
    /**
     * 容器环境
     *
     * @var Context
     */
    protected $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * 获取环境
     *
     * @return Context
     */
    public function context():Context
    {
        return $this->context;
    }

    /**
     * 监听事件
     *
     * @param string $name
     * @param \suda\framework\runnable\Runnable|\Closure|array|string $runnable
     * @return self
     */
    public function on(string $name, $runnable)
    {
        $this->context->get('event')->listen($name, $runnable);
        return $this;
    }

    /**
     * 请求页面
     *
     * @param string $method
     * @param string $uri
     * @param array $header
     * @return self
     */
    public function request(string $method, string $uri, array $header = [])
    {
        $request = new RawRequest;
        $request->server['remote-addr'] = '0.0.0.0';
        $request->server['request-uri'] = $uri;
        $request->server['request-method'] = $method;
        $request->header = $header;
        $this->context->set('request', new Request($request));
        return $this;
    }

    /**
     * 运行请求
     *
     * @return void
     */
    public function run()
    {
        $route = $this->context->get('route');
        $event = $this->context->get('event');
        $config = $this->context->get('config');
        $request = $this->context->get('request');
        $response = $this->context->get('response');

        $event->exec('service:load-config', [$config ,$this]);

        $event->exec('service:load-route', [$route , $this]);

        $result = $route->match($request, $response);

        if ($result !== null) {
            $event->exec('service:route:match::after', [$result, $request]);
        }
        
   
        $response = $route->run($request, $response, $result);

        if (!$response->isSended()) {
            $response->sendContent();
        }
    }
}
