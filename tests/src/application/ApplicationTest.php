<?php
namespace test\arrayobject;

use suda\phpunit\TestRequest;
use PHPUnit\Framework\TestCase;
use suda\framework\loader\Loader;
use suda\application\builder\ApplicationBuilder;

class ApplicationTest extends TestCase
{
    /**
     * @dataProvider getRoute
     * @param $expected
     * @param $parameter
     */
    public function testRouteParse($expected, $parameter)
    {
        $request = new TestRequest;
        $loader = new Loader;
        $application = ApplicationBuilder::build($loader, SUDA_APP, SUDA_DATA);
        $this->assertEquals($expected, $application->parseRouteName(...$parameter));
    }

    /**
     * @dataProvider getRouteName
     * @param $expected
     * @param $parameter
     * @throws \ReflectionException
     * @throws \suda\orm\exception\SQLException
     */
    public function testGetRouteName($expected, $parameter)
    {
        $request = new TestRequest;
        $loader = new Loader;
        $application = ApplicationBuilder::build($loader, SUDA_APP, SUDA_DATA);
        $application->loader()->register();
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
