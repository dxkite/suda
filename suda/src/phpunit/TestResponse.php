<?php
namespace suda\phpunit;

use SplFileObject;
use suda\framework\Request;
use suda\framework\Response;
use suda\framework\http\Cookie;
use suda\framework\http\Header;
use suda\framework\http\Stream;
use suda\framework\response\MimeType;
use suda\framework\http\stream\DataStream;
use suda\framework\response\ContentWrapper;

class TestResponse extends Response
{
    /**
     * 控制不允许输出
     *
     * @return void
     */
    public function end() {
        $this->sended = true;
    }
}
