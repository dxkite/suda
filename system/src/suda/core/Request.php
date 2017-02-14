<?php
namespace suda\core;
/**
* 单利模式
*/
class Request
{
    static $request=null;
    private function Request(){

    }

    public static function instance() {
        if (is_null(self::$request)){
            self::$request=new Request();
        }
        return self::$request;
    }
}