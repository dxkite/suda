<?php
namespace suda\application\processor;

use suda\framework\Request;
use suda\framework\Response;
use suda\application\Application;
use suda\application\processor\RequestProcessor;

/**
 * 响应
 */
class FileRequestProcessor implements RequestProcessor
{
    public function onRequest(Application $application, Request $request, Response $response)
    {
        $filename = $request->getAttribute('source');
        if (is_string($filename)) {
            $processor = new FileRangeProccessor($filename);
            $processor->onRequest($application, $request, $response);
        } else {
            $response->status(404);
        }
    }
}
