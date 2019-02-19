<?php
namespace test\server;

use suda\framework\Server;
use PHPUnit\Framework\TestCase;
use suda\framework\server\request\Builder;

class ServerRequestTest extends TestCase
{
    public function testBuild()
    {
        $request = Builder::createVirtual('GET', 'https://suda.org/helloworld?name=value', [], [], '113.244.57.120');
        $this->assertNotEquals($request->getPort(), 80);
        $this->assertEquals($request->getPort(), 443);
        $this->assertEquals($request->getRemoteAddr(), '113.244.57.120');
        $this->assertNull($request->json());
        $this->assertEquals($request->getQuery(), ['name' => 'value']);
    }
}
