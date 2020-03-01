<?php
namespace suda\application\debug;

use suda\framework\Request;
use suda\framework\Response;
use suda\application\Application;

/**
 * Class RequestDumper
 * @package suda\application
 */
class RequestDumpCatcher extends ExceptionCatcher
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
