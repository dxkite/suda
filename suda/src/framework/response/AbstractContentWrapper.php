<?php
namespace suda\framework\response;

use suda\framework\Request;
use suda\framework\Response;
use suda\framework\http\Stream;

/**
 * 响应接口
 */
abstract class AbstractContentWrapper
{

    /**
     * 类型
     *
     * @var string
     */
    protected $type;

    /**
     * 内容
     *
     * @var mixed
     */
    protected $content;

    public function __construct($content, string $type)
    {
        $this->content = $content;
        $this->type = $type;
    }

    /**
     * 获取内容
     *
     * @param \suda\framework\Response $response
     * @return \suda\framework\http\Stream
     */
    abstract public function getContent(Response $response): Stream;
}
