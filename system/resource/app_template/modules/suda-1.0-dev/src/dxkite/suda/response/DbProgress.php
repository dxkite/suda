<?php
namespace dxkite\suda\response;

use suda\core\Session;
use suda\core\Cookie;
use suda\core\Request;
use suda\core\Query;
use dxkite\suda\DBManager;

/**
* visit url /database-process as all method to run this class.
* you call use u('datebase_progress',Array) to create path.
* @template: default:db_progress.tpl.html
* @name: datebase_progress
* @url: /database-process
* @param:
*/
class DbProgress extends \suda\core\Response
{
    public function onRequest(Request $request)
    {
        $this->type('html');
        $option=$request->get()->option;
        $all=$request->get()->all('false');
        
        // DBManager::parseDTOs();
        // DBManager::createTables();
        // DBManager::importTables();
        DBManager::backupTables();
        // DBManager::deleteTables();
    }
}
