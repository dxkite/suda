<?php
namespace suda\application\processor;

use suda\framework\Request;
use suda\framework\Response;
use suda\application\processor\RequestProcessor;

/**
 * 响应
 */
class FileRequestProcessor implements RequestProcessor
{
    public function onRequest(Request $request, Response $response)
    {
        $response->sendFile($request->getAttribute('source'));
    }
}
