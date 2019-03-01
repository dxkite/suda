<?php
namespace test\server;

use SplFileInfo;
use suda\framework\Server;
use PHPUnit\Framework\TestCase;
use suda\framework\server\Response;
use suda\framework\server\request\Builder;

class ServerResponseTest extends TestCase
{
    /**
     * @dataProvider responseData
     */
    public function testBuild($content, $string)
    {
        $request = Builder::createVirtual('GET', 'https://suda.org/helloworld?name=value', [], [], '113.244.57.120');
        $response = new Response(200, $content);
        $this->assertEquals($response->getContent(), $string);
    }

    public function responseData() {
        return  [
            'null type' => [null, ''],
            'array type' => [['array'],'["array"]'],
            'file type' => [new SplFileInfo(__FILE__), \file_get_contents(__FILE__)],
            'html type' => ['hello', 'hello'],
            'int type' => [1, '1'],
        ];
    }
}
