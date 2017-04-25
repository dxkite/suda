<?php
namespace cn\atd3\response;

use suda\core\{Session,Cookie,Request,Query};
use cn\atd3\RouterManager;


/**
* visit url /router/list as all method to run this class.
* you call use u('router_list',Array) to create path.
* @template: default:router_list.tpl.html
* @name: router_list
* @url: /router/list
* @param:
*/
class RouterList extends \suda\core\Response
{
    public function onRequest(Request $request)
    {
        
        $delete=$request->get('delete');
        $module=$request->get('module');
        if ($delete && $module) {
            $this->set('result', true);
            $result=RouterManager::delete($module,$delete);
            $this->set('success',$result);
            $this->set('delete_info', $result?_T('删除路由 %s 成功!', $delete):_T('删除路由 %s 失败!', $delete));
            // Header('Location:'.u('router_list'));
        }
        
        $router=RouterManager::getInfo();
       
        return $this->display('suda:router_list', ['title'=>'路由列表', 'router'=>$router]);
    }
}
