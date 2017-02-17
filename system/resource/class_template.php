<?php
namespace __class_namespace__;
// use namespace
use_namespace('suda\\core');
/**
* @template: __template_path__
* @name: __router_name__
* @url: __create_url__
* @param: __param_mark__
*/
// Auto generate response class
class __class_name__ extends \suda\core\Response {
    public function onRequest(Request $request){
        // params
        __params_str__
        // param values array
        $value=__param_array__;
        // display template
        $this->display('__module__:__template_name__',['helloworld'=>'Hello,World!','value'=>$value]);
    }
}