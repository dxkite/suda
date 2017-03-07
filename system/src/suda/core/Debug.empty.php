<?php
namespace suda\core;
/**
* 非Debug模式的空Debug类
*/
class Debug
{
    public static function __callStatic($method, $args)
    {
    }
    public function __call($method, $args)
    {
    }
}
