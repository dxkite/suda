<?php
namespace test\arrayobject;

use suda\framework\Request;
use suda\framework\Debugger;
use suda\phpunit\TestRequest;
use suda\phpunit\TestResponse;
use PHPUnit\Framework\TestCase;
use suda\framework\loader\Loader;
use suda\framework\arrayobject\ArrayDotAccess;
use suda\application\builder\ApplicationBuilder;

class ApplicationTest extends TestCase
{
    public function testCreate()
    {
        $request = new TestRequest;
        $loader = new Loader;
        $application = ApplicationBuilder::build($loader, SUDA_APP);
        $application->load();
        $this->assertEquals('suda/welcome:1.0.0@setting:index', $application->getRouteName('index', 'welcome', 'setting'));
    }
}
