<?php

namespace test\framework;

use function foo\func;
use suda\framework\Event;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    public function testProcess()
    {
        $event = new Event();
        $event->add('test',function ($value, & $data){
            $data = true;
            return $value.' - processed';
        });
        $data = false;
        $value = 'test';
        try {
            $value = $event->process('test', $value, [&$data]);
            $this->assertEquals(true, $data);
            $this->assertEquals('test - processed', $value);
        } catch (\ReflectionException $e) {
        }
    }
}
