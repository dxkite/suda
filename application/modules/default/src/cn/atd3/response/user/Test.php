<?php
namespace cn\atd3\response\user;
use suda\core\Request;
// Auto generate response class
class Test extends \suda\core\Response {
    public function onRequest(Request $request){
        //Auto create params getter ...

        $this->display('default:user\test',['helloworld'=>'Hello,World!']);
    }
}