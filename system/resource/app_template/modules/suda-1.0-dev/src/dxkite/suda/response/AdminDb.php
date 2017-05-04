<?php
namespace dxkite\suda\response;

use suda\core\{Session,Cookie,Request,Query};
use dxkite\suda\DBManager;
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
        $backupname= $request->get()->current ?? DBManager::LASTER;
        $page=$this->page('suda:admin_db')
        ->set('title',_T('数据管理'))
        ->set('header_select','system_admin');

        
        $list=DBManager::readList();
        $read=DBManager::read($backupname);
        
        $page->set('backup_list',$list);
        $page->set('time',$read['time']);
        $page->set('current_name',$backupname);
        $page->set('current',$read['module'] ?? [] );
        
        return $page->render();
    }
}
