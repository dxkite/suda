<?php
namespace __class_namespace__;

use suda\core\{Session,Cookie,Request,Query};

/**
* visit url __create_url__ as __methods__ method to run this class.
* you call use u('__router_name__',Array) to create path.
* @template: __template_path__
* @name: __router_name__
* @url: __create_url__
* @param: __param_mark__
*/
class __class_name__ extends \__parent__
{
    public function onRequest(Request $request)
    {
        // params if had
        __params_str__;
        // param values array
        $value=__param_array__;
        // display template
        return $this->page('__module__:__template_name__')
        ->set('title','Welcome to use Suda!')
        ->set('helloworld','Hello,World!')
        ->set('value',$value)
        ->render();
    }
}
