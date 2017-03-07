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
class __class_name__ extends \__parent__
{
    public function onRequest(Request $request)
    {
        ob_clean();
        // 结束缓冲控制
        // self::obEnd();
        // params if had
        __params_str__;
        // param values array
        $values=__param_array__;
        $values=array_merge(['0%'=>'prepare.','11%'=>'prepare..','32%'=>'prepare...'],$values);
        $values=array_merge(['0%'=>'prepare.','11%'=>'prepare..','32%'=>'prepare...'],$values);
        foreach ($values as $name => $value){
            
            echo '<strong>value:'.$name.'</strong>::'.$value.'<br/>';
            ob_flush();
            flush();
            sleep(1);
        }
        
        while(true){
            echo 'runing...<br />';
            ob_flush();
            flush();
            sleep(1);
        }
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
