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
 * @version    1.2.3
 */

namespace dxkite\suda;

use suda\template\compiler\suda\Template;

class Dataset
{
    public static function header(Template $template)
    {
        /*
                    'database_list'=>['url'=>u('suda:database_list'),'name'=>__('数据管理')],
            'module_list'=>['url'=>u('suda:module_list'),'name'=>__('模块管理')],*/
        $header=[
            'router_list'=>['url'=>u('suda:router_list'),'name'=>__('路由管理')],
            'system_admin'=>['url'=>u('suda:system_admin'),'name'=>__('系统管理')],
        ];
        $template->set('header', $header);
    }
}
