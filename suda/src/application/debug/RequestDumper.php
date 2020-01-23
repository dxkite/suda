<?php
namespace suda\application\debug;

use Exception;
use Throwable;
use suda\framework\Request;
use suda\framework\Response;
use suda\application\Application;
use suda\framework\debug\DebugObject;
use suda\framework\filesystem\FileSystem;

/**
 * Class RequestDumper
 * @package suda\application
 */
class RequestDumper extends ExceptionCatcher
{
    /**
     * @var Response
     */
    protected $response;


    public function __construct(Application $application, Request $request, Response $response)
    {
        $context = [];
        $context['request'] = $request;
        $context['response'] = $response;
        parent::__construct($application, $context);
        $this->response = $response;
    }
}
