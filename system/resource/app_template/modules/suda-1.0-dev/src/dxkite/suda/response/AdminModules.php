<?php
namespace dxkite\suda\response;

use suda\core\{Session,Cookie,Request,Query};

/**
* visit url /system[/modules] as all method to run this class.
* you call use u('system_admin',Array) to create path.
* @template: default:admin_modules.tpl.html
* @name: system_admin
* @url: /system[/modules]
* @param: 
*/
class AdminModules extends \suda\core\Response
{
    public function onRequest(Request $request)
    {
        $page=$this->page('suda$1.0.0-dev@dxkite:admin_modules')
        ->set('title',_T('模块管理'))
        ->set('header_select','system_admin');
        
        return $page->render();
    }
}
