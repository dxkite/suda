<?php
namespace cn\atd3\response;

use suda\core\{Session,Cookie,Request,Query};

/**
* visit url / as all method to run this class.
* you call use u('default',Array) to create path.
* @template: default:index.tpl.html
* @name: default
* @url: /
* @param: 
*/
class Index extends \suda\core\Response
{
    public function onRequest(Request $request)
    {
        // params if had
        ;
        // param values array
        $value=array();
        // display template
        return $this->page('default$1.0.0-dev@dxkite:index')
        ->set('title','Welcome to use Suda!')
        ->set('helloworld','Hello,World!')
        ->set('value',$value)
        ->render();
    }
}
