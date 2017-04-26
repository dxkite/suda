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
        
        $page=$this->page('suda:admin_db')
        ->set('title',_T('数据管理'))
        ->set('header_select','system_admin');
        return $page->render();
    }
}
