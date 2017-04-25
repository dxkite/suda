<?php
namespace cn\atd3\response;

use suda\core\Session;
use suda\core\Cookie;
use suda\core\Request;
use suda\core\Query;
use cn\atd3\RouterManager;

/**
* visit url /router/add as all method to run this class.
* you call use u('router_add',Array) to create path.
* @template: default:router_add.tpl.html
* @name: router_add
* @url: /router/add
* @param:
*/
class RouterAdd extends \suda\core\Response
{
    public function onRequest(Request $request)
    {
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
            
            $result=RouterManager::add($method,$post->url,$post->class.'@'.$post->module,$post->router,
             strtolower($post->obcache)=='true',
             strtolower($post->response)=='admin',
             strtolower($post->type)=='json'
             );
            $this->set('class',$result['class']);
            $this->set('template',$result['template']);
            return $this->display('suda:router_add_ok');
        }
        $this->set('modules', RouterManager::getModules());
        $this->set('title', _T('模块添加'));
        return $this->display('suda:router_add');
    }
}
