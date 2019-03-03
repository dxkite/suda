<?php
namespace suda\framework\response\wrapper;

use suda\framework\Request;
use suda\framework\Response;
use suda\framework\response\AbstractContentWrapper;

/**
 * 响应包装器
 */
class JsonContentWrapper extends AbstractContentWrapper
{
    /**
     * 获取内容
     *
     * @param Response $response
     * @return \suda\framework\http\Stream|string
     */
    public function getContent(Response $response)
    {
        $response->setType('json');
        return \json_encode($this->content);
    }
}
