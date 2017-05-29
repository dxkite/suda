<?php
namespace dxkite\suda\response;

use suda\core\Session;
use suda\core\Cookie;
use suda\core\Request;
use suda\core\Query;
use dxkite\suda\RouterManager;

/**
* visit url /router/add as all method to run this class.
* you call use u('router_add',Array) to create path.
* @template: default:router_add.tpl.html
* @name: router_add
* @url: /router/add
* @param:
*/
class RouterAdd extends \dxkite\suda\ACResponse
{
    public function onAction(Request $request)
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
            
            $result=RouterManager::add($method, $post->url, $post->class.'@'.$post->module, $post->router,
             strtolower($post->response)=='admin',
             strtolower($post->type)=='json',
             strtolower($post->over)=='on'
             );
             
            return $this->page('suda:router_add_ok')->set('header_select', 'router_list')
           ->set('title', __('修改路由成功')) ->set('class', $result['class'])
            ->set('template', $result['template']??'无模板')->render();
        }

        $page=$this->page('suda:router_add')->set('header_select', 'router_list')
        ->set('modules', RouterManager::getModules())
        ->set('title', __('添加路由'));
        if ($request->get()->module) {
            $page->set('module_selected', $request->get()->module);
        }
        return $page->render();
    }
}
