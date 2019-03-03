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
     * @param Request $request
     * @param Response $response
     * @return Stream|string
     */
    abstract public function getContent(Request $request, Response $response);
}
