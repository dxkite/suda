<?php
namespace suda\framework\server\response\wrapper;

use suda\framework\server\Request;
use suda\framework\server\Response;
use suda\framework\server\response\AbstractContentWrapper;

/**
 * 响应包装器
 */
class NullContentWrapper extends AbstractContentWrapper
{
    public function __construct()
    {
        parent::__construct(null, 'null');
    }
    
    public function getContent(Request $request, Response $response): string
    {
        return '';
    }
}