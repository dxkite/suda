<?php
namespace dxkite\suda\response;

use suda\core\{Session,Cookie,Request,Query};

/**
* visit url /system/config/database as all method to run this class.
* you call use u('config_database',Array) to create path.
* @template: default:config_db.tpl.html
* @name: config_database
* @url: /system/config/database
* @param: 
*/
class ConfigDb extends \suda\core\Response
{
    public function onRequest(Request $request)
    {
        // params if had
        ;
        // param values array
        $value=array();
        // display template
        return $this->page('suda:config_db')
        ->set('title','Welcome to use Suda!')
        ->set('helloworld','Hello,World!')->set('header_select','system_admin')
        ->set('value',$value)
        ->render();
    }
}
