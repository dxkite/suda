<?php
namespace cn\atd3\response;

use suda\core\{Session,Cookie,Request,Query};

/**
* visit url / as all method to run this class.
* you call use u('index',Array) to create path.
* @template: default:index.tpl.html
* @name: index
* @url: /
* @param: 
*/
class Index extends \suda\core\Response
{
    public function onRequest(Request $request)
    {
        $page=$this->page('demo/default:index');

        // params if had
        ;
        // param values array
        $value=array();
        // display template

        $page->set('title', 'Welcome to use Suda!')
        ->set('helloworld', 'Hello,World!')
        ->set('value', $value);

        return $page->render();
    }
}
