<?php
namespace dxkite\suda\response;

use suda\core\Session;
use suda\core\Cookie;
use suda\core\Request;
use suda\core\Query;
use dxkite\suda\ModuleManager;
/**
* visit url /add/moduel as all method to run this class.
* you call use u('add_module',Array) to create path.
* @template: default:module_add.tpl.html
* @name: add_module
* @url: /add/moduel
* @param: 
*/
class ModuleAdd extends \dxkite\suda\ACResponse
{
    public function onAction(Request $request)
    {
        $page=$this->page('dxkite/suda:module_add');
        if ($request->isPost()){
            $p=$request->post();
            // var_dump($p);
            $result=ModuleManager::createModule($p->name,$p->version,$p->homepage,$p->locale,$p->namespace,$p->require,$p->authors,$p->discription);
            $this->setHeader('Location:'.u('suda:system_admin'));
        }
        $page->set('title', '添加模块')->set('header_select','system_admin');
        return $page->render();
    }
}
