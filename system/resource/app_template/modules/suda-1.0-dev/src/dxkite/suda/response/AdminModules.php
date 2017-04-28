<?php
namespace dxkite\suda\response;

use suda\core\Session;
use suda\core\Cookie;
use suda\core\Request;
use suda\core\Query;
use dxkite\suda\ModuleManager;

/**
* visit url /system[/modules] as all method to run this class.
* you call use u('system_admin',Array) to create path.
* @template: default:admin_modules.tpl.html
* @name: system_admin
* @url: /system[/modules]
* @param:
*/
class AdminModules extends \suda\core\Response
{
    public function onRequest(Request $request)
    {
        $page=$this->page('suda:admin_modules')
        ->set('title', _T('模块管理'))
        ->set('header_select', 'system_admin');
        if ($request->hasGet()) {
            ModuleManager::setModuleStatu($request->get()->module,
            strtolower($request->get()->set(''))==='off'?
            ModuleManager::MODULE_OFF:ModuleManager::MODULE_ON);
        }
        $module_infos=ModuleManager::getModulesInfo();
        $page->set('module_infos', $module_infos);
        return $page->render();
    }
}
