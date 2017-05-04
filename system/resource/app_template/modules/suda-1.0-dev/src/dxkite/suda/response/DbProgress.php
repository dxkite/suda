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
        // 操作
        $option=strtolower($request->get()->option);
        // 操作全部
        $all=strtolower($request->get()->all('no'));
        // 操作全部
        if ($all==='yes' || $request->get()->name) {
            if ($name=$request->get()->name){
                DBManager::archive($name);
            }
            if ($option==='recovery') {
                DBManager::importTables();
            } elseif ($option==='delete') {
                DBManager::deleteTables();
            } elseif ($option==='backup') {
                DBManager::backupTables();
            }
        } else {
            // 操作多个模块
            if ($request->isPost()) {
                $select=$request->post()->select([]);
                _D()->info($select);
                $tables=array_keys($select);
                if ($option==='recovery') {
                    DBManager::importTables($tables);
                } elseif ($option==='delete') {
                    DBManager::deleteTables($tables);
                } elseif ($option==='backup') {
                    DBManager::backupTables($tables);
                }
            }
            // 操作单个模块
            else if ($module=$request->get()->module){
                $tables=[$module];
                if ($option==='recovery') {
                    DBManager::importTables($tables);
                } elseif ($option==='delete') {
                    DBManager::deleteTables($tables);
                } elseif ($option==='backup') {
                    DBManager::backupTables($tables);
                }
            }
        }
    }
}
