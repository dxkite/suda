<?php
namespace suda\application;

use suda\framework\Response;
use Throwable;

/**
 * 错误Dumpper
 */
class DebugDumpper
{
    /**
     * 环境内容
     *
     * @var Response
     */
    protected $response;

    /**
     * 应用
     *
     * @var Application
     */
    protected $application;

    /**
     * 初始化
     */
    public function __construct(Application $application, Response $response)
    {
        $this->response = $response;
        $this->application = $application;
    }

    /**
     * 注册错误处理函数
     * @return self
     */
    public function register()
    {
        set_exception_handler([$this,'uncaughtException']);
        return $this;
    }

    /**
     * 异常托管
     *
     * @param Throwable $exception
     * @return void
     */
    public function uncaughtException($exception)
    {
        $this->application->debug()->addIgnoreTraces(__FILE__);
        $this->application->debug()->uncaughtException($exception);
        if ($this->response->isSended() === false) {
            $this->response->sendContent($exception);
            $this->response->end();
        }
    }
}
