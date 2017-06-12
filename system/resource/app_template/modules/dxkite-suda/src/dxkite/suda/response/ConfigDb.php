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

use suda\core\{Cookie,Request,Config};
use suda\archive\Query;
use suda\tool\ArrayHelper;
/**
* visit url /system/config/database as all method to run this class.
* you call use u('config_database',Array) to create path.
* @template: default:config_db.tpl.html
* @name: config_database
* @url: /system/config/database
* @param:
*/
class ConfigDb extends \dxkite\suda\ACResponse
{
    protected function checkConfig()
    {
        return (new Query('SET NAMES UTF8'))->good();
    }

    public function onAction(Request $request)
    {
        $page= $this->page('suda:config_db')
        ->set('title', __('配置数据库'))
        ->set('header_select', 'system_admin');
        
        $config=conf('database');

        // 验证数据库配置
        if ($request->isPost()) {
            $page->set('show', true);
            // 记得过滤
            Config::assign(['database'=>$request->post()->database]);
            // 验证配置是否可用
            if ($check=self::checkConfig()) {
                $config=$request->post()->database;
                ArrayHelper::export(RUNTIME_DIR.'/database.config.php', '_database_runtime', $request->post()->database);
            } else {
                $page->set('check', false);
                Config::assign(['database'=>$config]);
            }
        }
        return $page->render();
    }
}
