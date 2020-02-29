<?php

namespace suda\application\debug;

use Throwable;
use Exception;
use suda\framework\Context;
use suda\framework\debug\DebugObject;
use suda\application\ApplicationContext;
use suda\framework\filesystem\FileSystem;

class ExceptionCatcher
{
    /**
     * 应用
     *
     * @var ApplicationContext
     */
    protected $applicationContext;

    /**
     * @var array
     */
    protected $context;

    /**
     * ExceptionCatcher constructor.
     * @param ApplicationContext $application
     * @param array $context
     */
    public function __construct(ApplicationContext $application, array $context = [])
    {
        $this->applicationContext = $application;
        $this->context = $context;
    }

    /**
     * 注册错误处理函数
     * @return self
     */
    public function register()
    {
        set_exception_handler([$this, 'uncaughtException']);
        return $this;
    }

    /**
     * @return void
     */
    public static function restore() {
        restore_exception_handler();
    }

    /**
     * 异常托管
     *
     * @param Throwable $exception
     * @return void
     * @throws Exception
     */
    public function uncaughtException($exception)
    {
        $this->applicationContext->debug()->addIgnorePath(__FILE__);
        $this->applicationContext->debug()->uncaughtException($exception);
        $this->dumpThrowable($exception);
    }

    /**
     * @param Throwable $throwable
     */
    public function dumpThrowable($throwable)
    {
        $dumpPath = $this->applicationContext->conf('save-dump-path');
        if ($dumpPath !== null) {
            $this->context['application'] = $this->applicationContext;
            $dumper = [
                'time' => time(),
                'throwable' => $throwable,
                'context' => $this->context,
                'backtrace' => $throwable->getTrace(),
            ];
            $exceptionHash = md5($throwable->getFile() . $throwable->getLine() . $throwable->getCode());
            $path = $dumpPath . '/' . microtime(true) . '.' . substr($exceptionHash, 0, 8) . '.json';
            FileSystem::make($dumpPath);
            FileSystem::put($path, json_encode(
                new DebugObject($dumper),
                JSON_PRETTY_PRINT
                | JSON_UNESCAPED_UNICODE
            ));
        }
    }
}