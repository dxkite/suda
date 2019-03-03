<?php
namespace suda\framework\response\wrapper;

use suda\framework\Request;
use suda\framework\Response;
use suda\framework\response\AbstractContentWrapper;

/**
 * 响应包装器
 */
class NullContentWrapper extends AbstractContentWrapper
{
    public function __construct()
    {
        parent::__construct(null, 'null');
    }
    
    /**
     * 获取内容
     *
     * @param Request $request
     * @param Response $response
     * @return Stream|string
     */
    public function getContent(Request $request, Response $response)
    {
        return '';
    }
}
