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
    /**
     * @dataProvider getRoute
     */
    public function testRouteParse($expected, $parameter)
    {
        $request = new TestRequest;
        $loader = new Loader;
        $application = ApplicationBuilder::build($loader, SUDA_APP);
        $this->assertEquals($expected, $application->parseRouteName(...$parameter));
    }

    /**
     * @dataProvider getRouteName
     */
    public function testGetRouteName($expected, $parameter)
    {
        $request = new TestRequest;
        $loader = new Loader;
        $application = ApplicationBuilder::build($loader, SUDA_APP);
        $application->load();
        $this->assertEquals($expected, $application->getRouteName(...$parameter));
    }

    public function getRoute()
    {
        return [
            'simple name' => [ ['welcome','setting','index'], ['index', 'welcome', 'setting'] ],
            'simple @group:name' => [ ['welcome','setting','index'], ['@setting:index', 'welcome'] ],
            'simple module@group:name' => [ ['welcome','setting','index'], ['welcome@setting:index'] ],
            'module null name' => [ [null,'setting','index'], ['index', null , 'setting'] ],
            'module null @group:name' => [ [null,'setting','index'], ['@setting:index',null] ],
            'simple module@group:name' => [ ['welcome','setting','index'], ['welcome@setting:index'] ],
        ];
    }

    public function getRouteName()
    {
        return [
            'simple name' => [ 'suda/welcome:1.0.0@setting:index', ['index', 'welcome', 'setting'] ],
            'simple :name' => [ 'suda/welcome:1.0.0@setting:index', [':index', 'welcome', 'setting'] ],
            'simple @group:name' => [ 'suda/welcome:1.0.0@setting:index', ['@setting:index', 'welcome'] ],
            'simple module@group:name' => [ 'suda/welcome:1.0.0@setting:index', ['welcome@setting:index'] ],
        ];
    }
}
