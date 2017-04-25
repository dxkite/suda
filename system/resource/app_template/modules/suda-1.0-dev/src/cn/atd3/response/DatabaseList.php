<?php
namespace cn\atd3\response;

use suda\core\{Session,Cookie,Request,Query};

/**
* visit url /database as all method to run this class.
* you call use u('database',Array) to create path.
* @template: default:database_list.tpl.html
* @name: database
* @url: /database
* @param: 
*/
class DatabaseList extends \suda\core\Response
{
    public function onRequest(Request $request)
    {
        return $this->page('suda:database_list', ['header_select'=>'database_list','title'=>'数据库管理'])->render();
    }
}
