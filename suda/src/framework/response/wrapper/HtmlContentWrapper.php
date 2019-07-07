<?php
namespace suda\framework\response\wrapper;

use function is_object;
use suda\framework\Response;
use suda\framework\http\Stream;
use suda\framework\http\stream\StringStream;
use suda\framework\response\AbstractContentWrapper;

/**
 * 响应包装器
 */
class HtmlContentWrapper extends AbstractContentWrapper
{
    /**
     * 获取内容
     *
     * @param Response $response
     * @return Stream
     */
    public function getWrappedContent(Response $response): Stream
    {
        $response->setType('html');
        if (is_object($this->content)) {
            if ($this->content instanceof Stream) {
                return $this->content;
            }
        }
        return new StringStream($this->content);
    }
}
