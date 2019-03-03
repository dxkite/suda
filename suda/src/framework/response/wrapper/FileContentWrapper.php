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
     * @param Request $request
     * @param Response $response
     * @return \suda\framework\http\Stream|string
     */
    public function getContent(Request $request, Response $response)
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
