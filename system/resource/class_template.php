<?php
namespace #class_namespace#;

use suda\core\Request;
use suda\core\Session;
use suda\core\Cookie;
/**
* @template: #template_path#
* @name: #router_name#
* @url: #create_url#
* @param: #param_mark#
*/
// Auto generate response class
class #class_name# extends \suda\core\Response {
    public function onRequest(Request $request){
        #params_str#
        // param values array
        $value=#param_array#;
        // display template
        $this->display('#module#:#template_name#',['helloworld'=>'Hello,World!','value'=>$value]);
    }
}