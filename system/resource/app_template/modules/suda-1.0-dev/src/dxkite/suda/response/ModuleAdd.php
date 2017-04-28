<?php
namespace dxkite\suda\response;

use suda\core\Session;
use suda\core\Cookie;
use suda\core\Request;
use suda\core\Query;

/**
* visit url /add/moduel as all method to run this class.
* you call use u('add_module',Array) to create path.
* @template: default:module_add.tpl.html
* @name: add_module
* @url: /add/moduel
* @param: 
*/
class ModuleAdd extends \suda\core\Response
{
    public function onRequest(Request $request)
    {
        $page=$this->page('dxkite/suda:module_add');
        $page->set('title', 'Welcome to use Suda!')->set('header_select','system_admin');
        return $page->render();
    }
}
