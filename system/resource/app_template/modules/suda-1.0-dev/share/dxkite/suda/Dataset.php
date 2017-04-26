<?php
namespace dxkite\suda;
use suda\template\Template;


class Dataset
{
    public static function header(Template $template)
    {
        $header=[
            'router_list'=>['url'=>u('suda:router_list'),'name'=>_T('路由管理')],
            'database_list'=>['url'=>u('suda:database_list'),'name'=>_T('数据管理')],
            'module_list'=>['url'=>u('suda:module_list'),'name'=>_T('模块管理')],
            'system_admin'=>['url'=>u('suda:system_admin'),'name'=>_T('系统管理')],
        ];
        $template->set('header',$header);
    }
}
