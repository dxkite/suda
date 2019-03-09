<?php
namespace suda\application\exception\wrapper;

use suda\framework\Request;
use suda\framework\Response;
use suda\framework\http\Stream;
use suda\application\template\ExceptionTemplate;
use suda\framework\response\AbstractContentWrapper;

/**
 * 响应包装器
 */
class ExceptionContentWrapper extends AbstractContentWrapper
{
    /**
     * 获取内容
     *
     * @param Response $response
     * @return \suda\framework\http\Stream|string
     */
    public function getContent(Response $response)
    {
        $content = $this->content;
        return (new ExceptionTemplate($content))->__toString();
    }
}
