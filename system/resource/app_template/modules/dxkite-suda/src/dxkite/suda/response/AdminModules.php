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
use dxkite\suda\ModuleManager;

/**
* visit url /system[/modules] as all method to run this class.
* you call use u('system_admin',Array) to create path.
* @template: default:admin_modules.tpl.html
* @name: system_admin
* @url: /system[/modules]
* @param:
*/
class AdminModules extends \dxkite\suda\ACResponse
{
    public function onAction(Request $request)
    {
        $page=$this->page('suda:admin_modules')
        ->set('title', __('模块管理'))
        ->set('header_select', 'system_admin');
        if ($request->hasGet()) {
            ModuleManager::setModuleStatu($request->get()->module,
            strtolower($request->get()->set(''))==='off'?
            ModuleManager::MODULE_OFF:ModuleManager::MODULE_ON);
            $this->setHeader('Location:'.u('suda:system_admin'));
        }
        $module_infos=ModuleManager::getModulesInfo();
        $page->set('module_infos', $module_infos);
        return $page->render();
    }
}
