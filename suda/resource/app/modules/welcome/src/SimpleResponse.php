<?php

namespace suda\welcome\response;

use suda\framework\Request;
use suda\framework\Response;
use suda\application\processor\RequestProcessor;

class SimpleResponse extends RequestProcessor
{
    public function onRequest(Request $request, Response $response)
    {
        return 'hello world :' .date('Y-m-d H:i:s');
    }
}
