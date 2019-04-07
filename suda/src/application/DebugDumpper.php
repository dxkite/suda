<?php
namespace suda\application;

use suda\framework\Response;

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
        register_shutdown_function([$this,'uncaughtFatalError']);
        set_error_handler([$this,'uncaughtError']);
        set_exception_handler([$this,'uncaughtException']);
        return $this;
    }

    /**
     * 末异常处理
     *
     * @return void
     */
    public function uncaughtFatalError()
    {
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
        if ($e = error_get_last()) {
            $isFatalError = E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING;
            if ($e['type'] === ($e['type'] & $isFatalError)) {
                $this->uncaughtError($e['type'], $e['message'], $e['file'], $e['line']);
            }
        }
    }

    /**
     * 异常托管
     *
     * @param \Throwable $exception
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

    /**
     * 错误托管
     *
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @return void
     */
    public function uncaughtError($errno, $errstr, $errfile, $errline)
    {
        $this->uncaughtException(new \ErrorException($errstr, 0, $errno, $errfile, $errline));
    }
}
