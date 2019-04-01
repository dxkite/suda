<?php
namespace suda\application\wrapper;

use suda\framework\Request;
use suda\framework\Response;
use suda\framework\http\Stream;
use suda\framework\http\stream\StringStream;
use suda\application\template\ExceptionTemplate;
use suda\framework\response\AbstractContentWrapper;

/**
 * 响应包装器
 */
class TemplateWrapper extends AbstractContentWrapper
{
    /**
     * 获取内容
     *
     * @param Response $response
     * @return \suda\framework\http\Stream
     */
    public function getContent(Response $response):Stream
    {
        $content = $this->content;
        return new StringStream($content->getRenderedString());
    }
}
