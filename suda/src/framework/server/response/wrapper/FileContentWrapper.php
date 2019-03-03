<?php
namespace suda\framework\server\response\wrapper;

use suda\framework\server\Request;
use suda\framework\server\Response;
use suda\framework\server\response\AbstractContentWrapper;

/**
 * 响应包装器
 */
class FileContentWrapper extends AbstractContentWrapper
{
    public function getContent(Request $request, Response $response): string
    {
        $content = $this->content;
        if ($content->isFile()) {
            $response->setType($content->getExtension());
            $response->setHeader('Content-Disposition', 'attachment;filename="' . $content->getBasename().'"');
            $response->setHeader('Cache-Control', 'max-age=0');
            return file_get_contents($content->getRealPath());
        }
        throw new \Exception('wrappered SplFileInfo must be file');
    }
}
