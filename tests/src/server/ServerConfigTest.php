<?php
namespace test\server;

use suda\framework\Server;
use PHPUnit\Framework\TestCase;

class ServerConfigTest extends TestCase
{
    public function testConfigYamlOrJsonLoad()
    {
        $this->assertEquals(Server::config()->loadConfig(TEST_RESOURCE.'/configs/test.json'), [
            'name' => 'suda/v2wrapper',
            'version' => '1.0.0',
        ]);
    }

    public function testConfigLoad()
    {
        $this->assertEquals(Server::config()->load(TEST_RESOURCE.'/configs/test')->get('type'), 'yaml');
        $this->assertEquals(Server::config()->get('require.files'), [
            'hello.php',
            'world.php'
        ]);
    }
}
