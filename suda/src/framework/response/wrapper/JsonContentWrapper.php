<?php
namespace suda\framework\response\wrapper;

use function json_encode;
use suda\framework\Response;
use suda\framework\http\Stream;
use suda\framework\http\stream\StringStream;
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
     * @return Stream
     */
    public function getWrappedContent(Response $response): Stream
    {
        $response->setType('json');
        return new StringStream(json_encode($this->content));
    }
}
