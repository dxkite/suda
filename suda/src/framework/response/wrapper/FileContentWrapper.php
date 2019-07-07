<?php
namespace suda\framework\response\wrapper;

use suda\framework\Response;
use suda\framework\http\Stream;
use suda\framework\http\stream\DataStream;
use suda\framework\response\AbstractContentWrapper;

/**
 * 响应包装器
 */
class FileContentWrapper extends AbstractContentWrapper
{
    /**
     * 获取内容
     *
     * @param Response $response
     * @return Stream
     */
    public function getWrappedContent(Response $response): Stream
    {
        $content = $this->content;
        $response->setType($content->getExtension());
        $response->setHeader('Content-Disposition', 'attachment;filename="' . $content->getBasename().'"');
        $response->setHeader('Cache-Control', 'max-age=0');
        return new DataStream($content);
    }
}
