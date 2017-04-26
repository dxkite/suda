<?php
namespace dxkite\suda\response;

use suda\core\{Session,Cookie,Request,Query};

/**
* visit url /system/database as all method to run this class.
* you call use u('admin_database',Array) to create path.
* @template: default:admin_db.tpl.html
* @name: admin_database
* @url: /system/database
* @param: 
*/
class AdminDb extends \suda\core\Response
{
    public function onRequest(Request $request)
    {
        // params if had
        ;
        // param values array
        $value=array();
        // display template
        return $this->page('suda$1.0.0-dev@dxkite:admin_db')
        ->set('title','Welcome to use Suda!')
        ->set('helloworld','Hello,World!')
        ->set('value',$value)
        ->render();
    }
}
