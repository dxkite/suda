<?php
namespace suda\application\processor;

use suda\framework\Request;
use suda\framework\Response;

/**
 * 响应
 */
interface RequestProcessor
{
    public function onRequest(Request $request, Response $response);
}
