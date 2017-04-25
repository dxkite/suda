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
        $page=$this->page('suda:router_list', ['title'=>'路由列表']);
        $delete=$request->get('delete');
        $module=$request->get('module');
        if ($delete && $module) {
            $result=RouterManager::delete($module,$delete);
            $page->set('result', true);
            $page->set('success',$result);
            $page->set('delete_info', $result?_T('删除路由 %s 成功!', $delete):_T('删除路由 %s 失败!', $delete));
        }
        return $page->set('router',RouterManager::getInfo())->render();
    }
}
