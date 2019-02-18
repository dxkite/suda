<?php
namespace test\arrayobject;

use PHPUnit\Framework\TestCase;
use suda\component\arrayobject\ArrayDotAccess;

class ArrayAccessTest extends TestCase
{
    /**
     * @dataProvider arrayGetData
     */
    public function testGet($data, $getName, $expected)
    {
        $array = new ArrayDotAccess($data);
        $this->assertEquals($array[$getName], $expected);
    }

    /**
     * @dataProvider arrayGetData
     */
    public function testStaticGet($data, $getName, $expected)
    {
        $this->assertEquals(ArrayDotAccess::get($data, $getName), $expected);
    }

    public function testSet()
    {
        $array = new ArrayDotAccess([]);
        $array['1.2.3.4'] = 5;
        $this->assertEquals($array->toArray(), [1=>[2=>[3=>[4=>5]]]]);
    }

    public function testStaticSet()
    {
        $array = [];
        ArrayDotAccess::set($array, '1.2.3.4', 5);
        $this->assertEquals($array, [1=>[2=>[3=>[4=>5]]]]);
    }

    /**
     *
     * @dataProvider arrayExistData
     */
    public function testExist($data, $name, $expected)
    {
        $array = new ArrayDotAccess($data);
        $this->assertEquals(isset($array[$name]), $expected);
    }

    /**
     * @depends testExist
     * @dataProvider arrayUnsetData
     */
    public function testUnset($data, $name, $expected)
    {
        $array = new ArrayDotAccess([$data]);
        unset($array[$name]);
        $this->assertEquals(isset($array[$name]), $expected);
    }

    public function arrayGetData()
    {
        $data = [
                'a' => [
                    'b' => 1,
                    'c' => 2
                ],
                'b' => 1235,
                'c' => ['d' => [1,2,3]],
                'e' => [
                    'f' => 'hello world'
                ]
            ];
        return [
            'simple get a' => [$data, 'a', ['b' => 1,'c' => 2] ],
            'simple get b' => [$data, 'b', 1235],
            'dot get a.b' => [$data, 'a.b', 1],
            'dot get e.f' => [$data, 'e.f', 'hello world'],
            'dot get c.d' => [$data, 'c.d', [1,2,3]]
        ];
    }

    public function arrayUnsetData()
    {
        $data = [
                'a' => [
                    'b' => 1,
                    'c' => 2
                ],
                'b' => 1235,
                'c' => ['d' => [1,2,3]],
                'e' => [
                    'f' => 'hello world'
                ]
            ];
        return [
            'unset test a' => [$data, 'a', false ],
            'unset test e.f' => [$data, 'e.f', false],
        ];
    }

    public function arrayExistData()
    {
        $data = [
                'a' => [
                    'b' => 1,
                    'c' => 2
                ],
                'b' => 1235,
                'c' => ['d' => [1,2,3]],
                'e' => [
                    'f' => 'hello world'
                ]
            ];
        return [
            'simple test a' => [$data, 'a', true ],
            'simple test c.d' => [$data, 'c.d', true],
            'simple test d' => [$data, 'd', false],
        ];
    }
}
