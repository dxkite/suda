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
 * @version    since 1.2.4
 */

namespace dxkite\suda;

use suda\core\Router;
use suda\core\Application;
use suda\core\Storage;
use suda\tool\Json;

/**
* nothing
*
*/

class RouterManager
{
    protected static $routerinfos=null;
    protected static $configs=[];
    protected static $urltype=['int'=>'\d+','string'=>'[^\/]+','url'=>'.+'];

    /**
    * 删除路由
    */
    public static function delete(string $module, string $id, bool $deleteall=false)
    {
        $module=trim($module);
        $id=trim($id);
        $info=self::getInfo($module)[$id]??null;
        if (!$info) {
            return false;
        }
        $module_path=Application::getModulePath($module);
        $admin=$info['role']==='admin';
        $name=$info['name'];
        $router_file= $module_path.'/resource/config/router'.($admin?'_admin':'').'.json';
        debug()->info(__('路由文件:%s', $router_file));
        if (Storage::exist($router_file)) {
            $json=Json::loadFile($router_file);
        } else {
            $json=[];
        }
        if (!isset($json[$name])) {
            debug()->warning(__('无法找到路由%s', $name));
            return false;
        }
        $class_path= $module_path.'/src/'.$info['class'].'.php';
        if (($class_path=Storage::abspath($class_path)) && $deleteall) {
            debug()->info($class_path);
            Storage::remove($class_path);
        }
        unset($json[$name]);
        return Json::saveFile($router_file, $json);
    }

    public static function className(string $module, string $name)
    {
        $module=trim($module);
        $name=trim($name);
        $module_config=Application::getModuleConfig($module);
        $namespace=$module_config['namespace'] ?? conf('app.namespace');
        return  str_replace($namespace.'\\response\\', '', $name);
    }



    public static function add(array $method, string $url, string $router, string $router_id, bool $admin=false, bool $json=false, bool $overwrite=false)
    {
        // 获取URL中的变量
        $params=self::getParams($url);
        if (!preg_match('/^(.+?)@(.+?)$/', $router, $matchs)) {
            return false;
        }
        $return=[];
        // 解析变量
        list($router, $class_short, $module)=$matchs;
        // 激活模块
        $module_dir=Application::getModuleDir($module);
        $module_config=Application::getModuleConfig($module);
        // 路由位置
        $router_file=Application::getModulePath($module).'/resource/config/router'.($admin?'_admin':'').'.json';
        // 命名空间
        $namespace=$module_config['namespace']??conf('app.namespace');

        $class=$namespace.'\\response\\'.$class_short;

        // 构建模板
        $params_str=array();
        $params_mark='';
        $value_get='array(';
        foreach ($params as $param_name=>$param_type) {
            $params_str[]="\${$param_name}=\$request->get()->{$param_name}(".(preg_match('/int/i', $param_type)?'0':"'{$param_name}'").')';
            $params_mark.="{$param_name}:{$param_type},";
            $value_get.="'{$param_name}'=>\$request->get()->{$param_name}(".(preg_match('/int/i', $param_type)?'0':"'{$param_name}'")."),";
        }
        $value_get.=')';
        $params_str=implode(";\r\n\t\t", $params_str);
        
        $pos=strrpos($class, '\\');
        $class_namespace=substr($class, 0, $pos);
        $class_name=substr($class, $pos+1);
        $class_path=Application::getModulePath($module).'/src/'.$class_namespace;
        $class_file=$class_path.'/'.$class_name.'.php';
        $return['class']=$class_file;
        $template_name=self::createTplName(preg_replace('/^(.+)Response$/i','$1',$class_short));
        $template_file=Application::getModulePath($module).'/resource/template/default/'.$template_name.'.tpl.html';
        $class_template_file=MODULE_RESOURCE.'/data/'.($json?'/class_json.php':'/class_html.php');
        $class_template= Storage::get($class_template_file);
        
        $parent=$admin?
        $module_config['response']['admin']??conf('app.response.admin', 'suda\\core\\Response'):
        $module_config['response']['normal']??conf('app.response.normal', 'suda\\core\\Response');
        $class_template=str_replace(
            [
                '__class_namespace__',
                '__class_name__',
                '__params_str__',
                '__module__',
                '__template_name__',
                '__create_url__',
                '__template_path__',
                '__router_name__',
                '__param_mark__',
                '__param_array__',
                '__methods__',
                '__parent__'
            ],
            [
                $class_namespace,
                $class_name,
                $params_str,
                $module,
                $template_name,
                $url,
                'default:'.$template_name.'.tpl.html',
                $router_id,
                $params_mark,
                $value_get,
                count($method)>0?implode(',', $method):'all',
                $parent,
            ], $class_template);
        $template=Storage::get(MODULE_RESOURCE.'/data/template.html');
        $template=str_replace('__create_url__', $url, $template);
    
        // 写入类模板
        if (
            !Storage::exist($class_file) // 模板文件不存在
            ||(Storage::exist($class_file) && $overwrite) // 存在但是选择重写
        ) {
            Storage::path($class_path);
            Storage::put($class_file, $class_template);
        }

        // 写入模板
        if (
            !$json && //返回HTML
            (!Storage::exist($template_file) // 不存在模板
             || (Storage::exist($template_file) && $overwrite)) // 存在模板选择重写
            ) {
            Storage::path(dirname($template_file));
            Storage::put($template_file, $template);
            $return['template']=$template_file;
        }

        // 更新路由
        Storage::path(dirname($router_file));
        if (Storage::exist($router_file)) {
            $json=Json::loadFile($router_file);
        } else {
            $json=[];
        }
        $item=array(
            'class'=>$class,
            'visit'=>$url,
        );

        if (count($method)) {
            $item['method']=$method;
        }

        $json[$router_id]=$item;
        Json::saveFile($router_file, $json);
        
        return $return;
    }

        
    public static function getParams(string $url)
    {
        $urltype=self::$urltype;
        $types=array();
        $url=preg_replace('/([\/\.\\\\\+\*\[\^\]\$\(\)\=\!\<\>\-])/', '\\\\$1', $url);
        $url=preg_replace_callback('/\{(?:(\w+)(?::(\w+))?)\}/', function ($match) use (&$types, $urltype) {
            $param_name=$match[1]!==''?$match[1]:count($types);
            $param_type= $match[2] ?? 'string';
            $types[$param_name]=$param_type;
        }, $url);
        return $types;
    }

    public static function getRouter(string $module, string $id)
    {
        return self::getInfo($module)[$id]??null;
    }

    /**
    * 列出路由
    */
    public static function getInfo(string $gmod=null)
    {
        if(is_null(self::$routerinfos)){
            $routerinfos=Router::getInstance()->getRouters();
            foreach($routerinfos as $id=>$infos){
                self::$routerinfos[$infos->getModule()][$id]=$infos;
            }
        }
        return $gmod?self::$routerinfos[$gmod]:self::$routerinfos;
    }

    protected static function createTplName(string $name)
    {
        $name=strtolower(preg_replace('/([A-Z])/', '_$1', $name));
        $names=explode('\\', $name);
        foreach ($names as $index=>$piece) {
            $names[$index]=trim($piece, '_');
        }
        return  implode('/', $names);
    }
}
