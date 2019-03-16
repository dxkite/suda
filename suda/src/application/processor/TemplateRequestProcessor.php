<?php
namespace suda\application\processor;

use suda\framework\Request;
use suda\framework\Response;
use suda\application\processor\RequestProcessor;

/**
 * 响应
 */
class TemplateRequestProcessor implements RequestProcessor
{
    public function onRequest(Application $application, Request $request, Response $response)
    {
        $response->send($request->getAttribute('template'));
    }
}
