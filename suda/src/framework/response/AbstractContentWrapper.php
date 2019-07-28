<?php /** @noinspection ALL */

namespace suda\framework\response;

use suda\framework\Request;
use suda\framework\Response;
use suda\framework\http\Stream;

/**
 * 响应接口
 */
abstract class AbstractContentWrapper implements ContentWrapperInterface
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

    /**
     * AbstractContentWrapper constructor.
     * @param $content
     * @param string $type
     */
    public function __construct($content, string $type)
    {
        $this->content = $content;
        $this->type = $type;
    }
}
