<?php

namespace suda\welcome\response;

use suda\framework\Request;
use suda\framework\Response;
use suda\welcome\table\TestTable;
use suda\application\processor\RequestProcessor;

class SimpleResponse implements RequestProcessor
{
    public function onRequest(Request $request, Response $response)
    {
        $source = $request->getAttribute('context')->get('data-source');
        $table = new TestTable($source);
        return 'hello world :' .date('Y-m-d H:i:s');
    }
}
