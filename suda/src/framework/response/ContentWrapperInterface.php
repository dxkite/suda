<?php


namespace suda\framework\response;

use suda\framework\http\Stream;
use suda\framework\Response;

interface ContentWrapperInterface
{
    /**
     * 获取已经包装成输出流的内容
     *
     * @param Response $response
     * @return Stream
     */
    public function getWrappedContent(Response $response): Stream;
}
