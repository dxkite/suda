<?php
namespace suda\framework\response\wrapper;

use suda\framework\Request;
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
     * @param \suda\framework\Response $response
     * @return \suda\framework\http\Stream
     */
    public function getContent(Response $response): Stream
    {
        $response->setType('html');
        if (\is_object($this->content)) {
            if ($this->content instanceof Stream) {
                return $this->content;
            }
        }
        return new StringStream($this->content);
    }
}
