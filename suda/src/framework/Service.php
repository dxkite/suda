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
     * 运行请求
     *
     * @return void
     */
    public function run()
    {
        $this->context->get('event')->exec('service:on:load-config', [$this->context->get('config') ,$this]);
        $this->context->get('event')->exec('service:on:load-route', [$this->context->get('route') , $this]);
        $response = $this->context->get('route')->match($this->context->get('request'), $this->context->get('response'));
        if (!$response->isSended()) {
            $response->sendContent();
        }
    }
}
