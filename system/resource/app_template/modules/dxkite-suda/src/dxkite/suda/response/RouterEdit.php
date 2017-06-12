<?php
/**
 * Suda FrameWork
 *
 * An open source application development framework for PHP 7.0.0 or newer
 * 
 * Copyright (c)  2017 DXkite
 *
 * @category   PHP FrameWork
 * @package    Suda
 * @copyright  Copyright (c) DXkite
 * @license    MIT
 * @link       https://github.com/DXkite/suda
 * @version    1.2.4
 */

namespace dxkite\suda\response;

use suda\core\Session;
use suda\core\Cookie;
use suda\core\Request;
use suda\core\Query;
use dxkite\suda\RouterManager;

/**
* visit url /router/edit as all method to run this class.
* you call use u('router_edit',Array) to create path.
* @template: default:router_edit.tpl.html
* @name: router_edit
* @url: /router/edit
* @param:
*/
class RouterEdit extends \dxkite\suda\ACResponse
{
    public function onAction(Request $request)
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
            // 新的路由ID和原先的不一样
            if ($post->name!=$post->router){
                RouterManager::delete($post->module,$post->name,strtolower($post->new)=='on');
            }
            RouterManager::add($method, $post->url,
            RouterManager::className($post->module,$post->class).'@'.$post->module,
            $post->router,
            strtolower($post->role)=='admin',
             false,
             strtolower($post->new)=='on');
             $this->setHeader('Location:'.u('suda:router_list'));
        }

        if ($edit && $module) {
            $page=$this->page('suda:router_edit');
            $router=RouterManager::getRouter($module, $edit);
            $page->set('module', $module);
            $page->set('router', $edit);
            $page->set('class', RouterManager::className($module,$router['class']));
            $page->set('visit', RouterManager::urlPrefix($module, strtolower($router['role'])=='admin', $router['visit']));
            $page->set('role', $router['role']);
            $methods=['ALL'=>false,'GET'=>false,'POST'=>false,'PUT'=>false,'DELETE'=>false];
            if (isset($router['method'])) {
                foreach ($router['method'] as $method) {
                    $methods[strtoupper($method)]=true;
                }
            } else {
                $methods['ALL']=true;
            }
            $page->set('title',__('编辑路由 %s',$edit));
            $page->set('method', $methods)->set('header_select', 'router_list');
            $page->set('modules', RouterManager::getModules());
            return $page->render();
        }
    }
}
