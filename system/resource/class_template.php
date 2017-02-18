<?php
namespace __class_namespace__;

// use namespace
use suda\core\Request;
// database query
use suda\core\Query;
// site cookie
use suda\core\Cookie;
// site session
use suda\core\Session;

/**
* visit url __create_url__ as __methods__ method to run this class.
* you call use _I('__router_name__',Array) to create path.
* @template: __template_path__
* @name: __router_name__
* @url: __create_url__
* @param: __param_mark__
*/
class __class_name__ extends \suda\core\Response
{
    public function onRequest(Request $request)
    {
        // params if had
        __params_str__;
        // param values array
        $value=__param_array__;
        // display template
        return $this->display('__module__:__template_name__', ['helloworld'=>'Hello,World!', 'value'=>$value]);
    }

    // pretest router 
    public function onPreTest($router):bool
    {
        return true;
    }

    // action when error
    public function onPreTestError($router)
    {
        echo 'onPreTestError';
        return true;
    }
}
