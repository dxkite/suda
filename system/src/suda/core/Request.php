<?php
namespace suda\core;
/**
* 单利模式
*/
class Request
{
    static $request=new Request();
    private function Request(){

    }

    public static function doRequest() {
        return $request;
    }
}