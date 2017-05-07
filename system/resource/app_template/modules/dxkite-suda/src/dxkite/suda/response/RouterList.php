<?php
namespace dxkite\suda\response;

use suda\core\Session;
use suda\core\Cookie;
use suda\core\Request;
use suda\core\Query;
use dxkite\suda\RouterManager;

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
        $page=$this->page('suda:router_list', ['title'=>'路由列表'])->set('header_select', 'router_list');
        $delete=$request->get('delete');
        $module=$request->get('module');
        if ($delete && $module) {
            $result=RouterManager::delete($module, $delete,strtolower($request->get()->all('no'))==='yes');
            $this->setHeader('Location:'.u('suda:router_list'));
        }
        return $page->set('router', RouterManager::getInfo())->render();
    }
}
