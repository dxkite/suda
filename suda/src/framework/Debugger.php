<?php

namespace suda\framework;

use function constant;
use ErrorException;
use function is_writable;
use ReflectionException;
use function restore_error_handler;
use function restore_exception_handler;
use function set_error_handler;
use function set_exception_handler;

use suda\framework\debug\Debug;
use suda\framework\runnable\Runnable;
use suda\framework\context\PHPContext;
use suda\framework\filesystem\FileSystem;
use suda\framework\debug\log\LoggerInterface;
use suda\framework\debug\log\logger\FileLogger;
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
     * @var PHPContext
     */
    protected $context;

    /**
     * 初始化
     */
    public function __construct()
    {
        $this->setLogger(new NullLogger);
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
     * 创建调式工具
     *
     * @param PHPContext $context
     * @return Debugger
     */
    public function load(PHPContext $context): Debugger
    {
        $this->applyConfig([
            'start-time' => constant('SUDA_START_TIME'),
            'start-memory' => constant('SUDA_START_MEMORY'),
        ]);
        $this->context = $context;
        return $this;
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
                $errorHander = set_error_handler(null);
                if ($errorHander !== null) {
                    $errorHander($e['type'], $e['message'], $e['file'], $e['line']);
                }
                restore_error_handler();
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
        $severity =
            1 * E_ERROR |
            1 * E_WARNING |
            0 * E_PARSE |
            1 * E_NOTICE |
            0 * E_CORE_ERROR |
            1 * E_CORE_WARNING |
            0 * E_COMPILE_ERROR |
            1 * E_COMPILE_WARNING |
            0 * E_USER_ERROR |
            1 * E_USER_WARNING |
            1 * E_USER_NOTICE |
            0 * E_STRICT |
            0 * E_RECOVERABLE_ERROR |
            0 * E_DEPRECATED |
            0 * E_USER_DEPRECATED;
        $exception = new ErrorException($errstr, $errno, $errno, $errfile, $errline);
        if ($exception->getSeverity() & $severity === 0) {
            throw $exception;
        } else {
            $exceptionHandler = set_exception_handler(null);
            if ($exceptionHandler !== null) {
                $exceptionHandler($exception);
            }
            restore_exception_handler();
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
