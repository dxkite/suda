<?php
namespace suda\application;

use Exception;
use phpDocumentor\Reflection\File;
use suda\framework\debug\DebugObject;
use suda\framework\filesystem\FileSystem;
use suda\framework\Request;
use suda\framework\Response;
use Throwable;

/**
 * Class DebugDumper
 * @package suda\application
 */
class DebugDumper
{
    /**
     * 应用
     *
     * @var Application
     */
    protected $application;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * DebugDumper constructor.
     * @param Application $application
     * @param Request $request
     * @param Response $response
     */
    public function __construct(Application $application, Request $request, Response $response)
    {
        $this->application = $application;
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * 注册错误处理函数
     * @return $this
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
     * @throws Exception
     */
    public function uncaughtException($exception)
    {
        $this->application->debug()->addIgnorePath(__FILE__);
        $this->application->debug()->uncaughtException($exception);
        $this->dumpThrowable($exception);
        if ($this->response->isSend() === false) {
            $this->response->sendContent($exception);
            $this->response->end();
        }
    }

    /**
     * @param Throwable $throwable
     */
    public function dumpThrowable($throwable)
    {
        $dumper = [
            'time' => time(),
            'throwable' => $throwable,
            'context' => [
                'application' => $this->application,
                'request' => $this->request,
                'response' => $this->response,
            ],
            'backtrace' => $throwable->getTrace(),
        ];
        $dumpPath = $this->application->getDataPath().'/logs/dump';
        $exceptionHash = md5($throwable->getFile().$throwable->getLine().$throwable->getCode());
        $path = $dumpPath.'/'.microtime(true).'.'.substr($exceptionHash, 0, 8).'.json';
        FileSystem::make($dumpPath);
        FileSystem::put($path, json_encode(
            new DebugObject($dumper),
            JSON_PRETTY_PRINT
            | JSON_UNESCAPED_UNICODE
        ));
    }
}
