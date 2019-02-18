<?php
namespace test\arrayobject;

use PHPUnit\Framework\TestCase;
use suda\component\arrayobject\RecursiveArrayDocAccess;

class RecursiveArrayDocAccessTest extends TestCase
{

    public function testGet()
    {
        $array = new RecursiveArrayDocAccess([1,2 =>[3,4,5,6]]);
        $this->assertEquals($array[2] instanceof RecursiveArrayDocAccess, true);
    }
}
