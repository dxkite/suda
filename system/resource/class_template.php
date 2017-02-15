<?php
namespace #class_namespace#;

use suda\core\Request;
use suda\core\Session;
use suda\core\Cookie;

// Auto generate response class
class #class_name# extends \suda\core\Response {
    public function onRequest(Request $request){
        #params_str#
        $this->display('#module#:#template_name#',['helloworld'=>'Hello,World!']);
    }
}