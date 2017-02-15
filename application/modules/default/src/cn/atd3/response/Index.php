<?php
namespace cn\atd3\response;
use suda\core\Request;
use suda\core\Cache;

class Index extends \suda\core\Response {
    public function onRequest(Request $request){
        Cache::set('name','dxkite');
        $this->display('default:helloworld',['helloworld'=>'Hello,World!']);
    }
}