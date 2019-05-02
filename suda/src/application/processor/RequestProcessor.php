<?php
namespace suda\application\processor;

use suda\framework\Request;
use suda\framework\Response;
use suda\application\Application;

/**
 * 响应
 */
interface RequestProcessor
{
    /**
     * @param Application $application
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    public function onRequest(Application $application, Request $request, Response $response);
}
