<?php
namespace suda\framework\response\wrapper;

use suda\framework\Request;
use suda\framework\Response;
use suda\framework\http\Stream;
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
     * @return \suda\framework\http\Stream|string
     */
    public function getContent(Response $response)
    {
        $response->setType('html');
        if (\is_object($this->content)) {
            if ($this->content instanceof Stream) {
                return $this->content;
            }
            return $this->content->__toString();
        }
        return $this->content;
    }
}
