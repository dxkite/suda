<?php
namespace suda\framework\response\wrapper;

use suda\framework\Request;
use suda\framework\Response;
use suda\framework\http\StringStream;
use suda\framework\response\AbstractContentWrapper;

/**
 * 响应包装器
 */
class JsonContentWrapper extends AbstractContentWrapper
{
    /**
     * 获取内容
     *
     * @param \suda\framework\Response $response
     * @return \suda\framework\http\Stream
     */
    public function getContent(Response $response): Stream
    {
        $response->setType('json');
        return new StringStream(\json_encode($this->content));
    }
}
