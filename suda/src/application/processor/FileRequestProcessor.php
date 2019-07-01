<?php
namespace suda\application\processor;

use Exception;
use suda\framework\Request;
use suda\framework\Response;
use suda\application\Application;

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
            try {
                $processor->onRequest($application, $request, $response);
            } catch (Exception $e) {
                $response->status(500);
            }
        } else {
            $response->status(404);
        }
    }
}
