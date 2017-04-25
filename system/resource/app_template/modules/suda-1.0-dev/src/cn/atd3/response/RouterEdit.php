<?php
namespace cn\atd3\response;

use suda\core\Session;
use suda\core\Cookie;
use suda\core\Request;
use suda\core\Query;
use cn\atd3\RouterManager;

/**
* visit url /router/edit as all method to run this class.
* you call use u('router_edit',Array) to create path.
* @template: default:router_edit.tpl.html
* @name: router_edit
* @url: /router/edit
* @param:
*/
class RouterEdit extends \suda\core\Response
{


    public function onRequest(Request $request)
    {
        $edit=$request->get('edit');
        $module=$request->get('module');
        if ($request->isPost()) {
            $post=$request->post();
            $method=$request->post()->method([]);
            // TODO 过滤接口
            if (!is_array($method)) {
                if (strtoupper($method)=='ALL') {
                    $method=[];
                } else {
                    $method=[$method];
                }
            }
            RouterManager::add($method,$post->url, RouterManager::className($post->class).'@'.$post->module,$post->router);
            return $this->display('suda:router_edit_ok');
        }

        if ($edit && $module) {
            $page=$this->page('suda:router_edit');
            $router=RouterManager::getRouter($module, $edit);
            $page->set('module', $module);
            $page->set('router', $edit);
            $page->set('class', RouterManager::className($router['class']));
            $page->set('visit', RouterManager::urlPrefix($module,strtolower($router['role'])=='admin',$router['visit']));
            $page->set('role', $router['role']);
            $methods=['ALL'=>false,'GET'=>false,'POST'=>false,'PUT'=>false,'DELETE'=>false];
            if (isset($router['method'])) {
                foreach ($router['method'] as $method) {
                    $methods[strtoupper($method)]=true;
                }
            } else {
                $methods['ALL']=true;
            }
            $page->set('method', $methods);
            $page->set('modules', RouterManager::getModules());
            return $page->render();
        }   
    }
}
