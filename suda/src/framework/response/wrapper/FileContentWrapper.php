<?php
namespace suda\framework\response\wrapper;

use suda\framework\Request;
use suda\framework\Response;
use suda\framework\http\Stream;
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
     * @return \suda\framework\http\Stream
     */
    public function getContent(Response $response): Stream
    {
        $content = $this->content;
        if ($content->isFile()) {
            $response->setType($content->getExtension());
            $response->setHeader('Content-Disposition', 'attachment;filename="' . $content->getBasename().'"');
            $response->setHeader('Cache-Control', 'max-age=0');
            return new Stream($content->getRealPath());
        }
        throw new \Exception('wrappered SplFileInfo must be file');
    }
}
