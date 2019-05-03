<?php
namespace suda\application\processor;

use suda\framework\Request;
use suda\framework\Response;
use suda\application\Application;

/**
 * 响应
 */
class TemplateRequestProcessor implements RequestProcessor
{
    public function onRequest(Application $application, Request $request, Response $response)
    {
        $template = $request->getAttribute('template');
        if (is_string($template)) {
            return $application->getTemplate($template, $request, $application->getRunning()->getFullName());
        } else {
            $response->status(404);
            return null;
        }
    }
}
