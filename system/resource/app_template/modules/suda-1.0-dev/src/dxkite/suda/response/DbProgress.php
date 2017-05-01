<?php
namespace dxkite\suda\response;

use suda\core\{Session,Cookie,Request,Query};

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
        $this->type('txt');
        for($i=0;$i<=10;$i++){
            $this->send('process '.$i.'%<br/>');
            sleep(1);
        }
    }
    public function backupAll(){

    }
    public function send(string $message){
        echo $message;
        echo str_repeat(' ',4096);
        flush();
        ob_flush();
    }
}
