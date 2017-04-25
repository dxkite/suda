<?php
namespace cn\atd3\response;

use suda\core\{Session,Cookie,Request,Query};

/**
* visit url / as all method to run this class.
* you call use u('index',Array) to create path.
* @template: default:suda.tpl.html
* @name: index
* @url: /
* @param: 
*/
class Suda extends \suda\core\Response
{
    public function onRequest(Request $request)
    {
        $router=\suda\core\Router::getInstance()->getRouteInfo();
        return $this->display('suda:suda', ['title'=>'框架管理面板','router'=>$router]);
    }
}
