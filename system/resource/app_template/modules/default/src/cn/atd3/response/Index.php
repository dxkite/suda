<?php
namespace cn\atd3\response;

// use namespace
use suda\core\Request;
// database query
use suda\core\Query;
// site cookie
use suda\core\Cookie;
// site session
use suda\core\Session;

/**
* the response when visit url 
* @template: default:index.tpl.html
* @name: default
* @url: /
* @param: 
*/
class Index extends \suda\core\Response {
    public function onRequest(Request $request){
        // params
        ;
        // param values array
        $value=array();
        // display template
        $this->display('default:index',['helloworld'=>'Hello,World!','value'=>$value]);
    }
}