<?php

namespace suda\framework;


use ErrorException;
use Psr\Log\LoggerInterface;
use suda\framework\debug\Debug;
use suda\framework\debug\log\logger\NullLogger;
use Throwable;

/**
 * 调试器
 */
class Debugger extends Debug
{
    /**
     * 环境内容
     *
     * @var Context
     */
    protected $context;

    /**
     * 初始化
     * @param Context $context
     * @param LoggerInterface $logger
     */
    public function __construct(Context $context, LoggerInterface $logger)
    {
        $this->context = $context;
        $this->logger = $logger;
        $this->applyConfig([
            'start-time' => defined('SUDA_START_TIME') ? constant('SUDA_START_TIME') : microtime(true),
            'start-memory' => defined('SUDA_START_MEMORY') ? constant('SUDA_START_MEMORY') : memory_get_usage(),
        ]);
        $this->context = $context;
        $this->timing = [];
        $this->timeRecord = [];
        $context->event()->listen('response::before-send', [$this, 'writeTiming']);
    }

    /**
     * 注册错误处理函数
     * @return $this
     */
    public function register()
    {
        register_shutdown_function([$this, 'uncaughtFatalError']);
        set_error_handler([$this, 'uncaughtError']);
        set_exception_handler([$this, 'uncaughtException']);
        return $this;
    }

    /**
     * @param Response $response
     */
    public function writeTiming(Response $response)
    {
        $output = $this->context->getConfig()->get('response-timing', true);
        if ($output) {
            $timing = [];
            foreach ($this->timing as $name => $info) {
                $time = $info['time'];
                $desc = $info['description'];
                $ms = number_format($time * 1000, 3);
                if (strlen($desc)) {
                    $timing[] = $name . ';desc="' . $desc . '";dur=' . $ms;
                } else {
                    $timing[] = $name . ';dur=' . $ms;
                }
            }
            $response->setHeader('server-timing', implode(',', $timing));
        }
    }

    /**
     * 获取原始记录器
     *
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
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
                $handler = set_error_handler(null);
                if ($handler !== null) {
                    $handler($e['type'], $e['message'], $e['file'], $e['line']);
                }
                restore_error_handler();
            } else {
                $this->error($e['message'], ['exception' => $e]);
            }
        }
    }

    /**
     * 异常托管
     *
     * @param Throwable $exception
     * @return void
     */
    public function uncaughtException($exception)
    {
        $this->error($exception->getMessage(), ['exception' => $exception]);
    }

    /**
     * 错误托管
     *
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @return void
     * @throws ErrorException
     */
    public function uncaughtError($errno, $errstr, $errfile, $errline)
    {
        $isFatalError = E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING;
        $exception = new ErrorException($errstr, $errno, $errno, $errfile, $errline);
        if ($exception->getSeverity() & $isFatalError === 0) {
            $exceptionHandler = set_exception_handler(null);
            // 有上一级非默认处理器
            if ($exceptionHandler !== null) {
                $exceptionHandler($exception);
                restore_exception_handler();
            } else {
                throw $exception;
            }
        } else {
            $this->warning($errstr, ['exception' => $exception]);
        }
    }

    public function getDefaultConfig(): array
    {
        return [
            'log-format' => '%time-format% - %memory-format% [%level%] %file%:%line% %message%',
            'start-time' => 0,
            'start-memory' => 0,
        ];
    }

    /**
     * 设置忽略前缀
     *
     * @return array
     */
    public function getIgnoreTraces(): array
    {
        $trace = parent::getIgnoreTraces();
        $trace[] = __FILE__;
        return $trace;
    }
}
