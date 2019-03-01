<?php
namespace suda\framework\server\response;

use suda\framework\server\Request;
use suda\framework\server\Response;

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

    abstract public function getContent(Request $request, Response $response): string;
}
