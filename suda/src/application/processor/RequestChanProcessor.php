<?php


namespace suda\application\processor;


use suda\application\Application;
use suda\framework\Request;
use suda\framework\Response;

interface RequestChanProcessor
{
    public function onRequest(Application $application, Request $request, Response $response, RequestProcessor $next);
}