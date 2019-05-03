<?php
namespace suda\phpunit;

use suda\framework\Response;

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
