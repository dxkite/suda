<?php
namespace suda\framework\response\wrapper;

use suda\framework\Request;
use suda\framework\Response;
use suda\framework\response\AbstractContentWrapper;

/**
 * 响应包装器
 */
class HtmlContentWrapper extends AbstractContentWrapper
{
    /**
     * 获取内容
     *
     * @param Request $request
     * @param Response $response
     * @return \suda\framework\http\Stream|string
     */
    public function getContent(Request $request, Response $response)
    {
        $response->setType('html');
        return $this->content;
    }
}
