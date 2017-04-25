<?php
namespace cn\atd3;

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
    protected static $routerinfos=[];

    /**
    * 删除路由
    */
    public static function delete(string $module, string $id)
    {
        $info=self::getInfo($module)[$id]??null;
        if (!$info) {
            return false;
        }
        $module_dir=Application::getModuleDir($module);
        $admin=$info['role']==='admin';
        $name=$info['name'];
        $router_file=MODULES_DIR.'/'.$module_dir.'/resource/config/router'.($admin?'_admin':'').'.json';
        _D()->info(_T('路由文件:%s', $router_file));
        if (Storage::exist($router_file)) {
            $json=Json::loadFile($router_file);
        } else {
            $json=[];
        }
        if (!isset($json[$name])) {
            _D()->waring(_T('无法找到路由%s', $name));
            return false;
        }
        unset($json[$name]);
        return Json::saveFile($router_file, $json);
    }

    public static function className(string $name)
    {
        $namespace=conf('module.namespace', conf('app.namespace'));
        return  str_replace($namespace.'\\response\\', '', $name);
    }

    public static function urlPrefix(string $module, bool $admin, string $url)
    {
        $prefix=Application::getModulePrefix($module);
        if ($prefix){
             $admin_prefix='';
            if (is_array($prefix)) {
                $admin_prefix=$prefix['admin'] ?? array_shift($prefix);
                $prefix=$prefix['simple'] ?? array_shift($prefix);
            }
            if ($admin){
                return  substr($url,strlen('/'.$admin_prefix));
            }
            return  substr($url,strlen('/'.$prefix));
        }
        return $url;
    }
    
    public static function getRouter(string $module, string $id)
    {
        return self::getInfo($module)[$id]??null;
    }
    public static function getModules()
    {
        return Application::getLiveModules();
    }
    /**
    * 列出路由
    */
    public static function getInfo(string $gmod=null)
    {
        self::$routerinfos=[];
        $modules=conf('app.modules', []);
        foreach ($modules as $module) {
            self::load($module);
        }
        if (is_null($gmod)) {
            return self::$routerinfos;
        }
        return self::$routerinfos[$gmod]??null;
    }

    public static function add(array $method, string $url, string $class, string $router=null, bool $ob =true, bool $admin=false, bool $json=false)
    {
        return Router::visit($method,  $url,  $class, $router,  $ob,  $admin, $json);
    }
    /* getInfo 辅助函数 */
    private static function loadModuleJson(string $module, string $jsonfile)
    {
        $routers=Json::loadFile($jsonfile);
        $router=[];
        foreach ($routers as $name => $value) {
            $router[$name]=$value;
            $router[$name]['name']=$name;
        }
        return $router;
    }
    private static function load(string $module)
    {
        $simple_routers=[];
        $admin_routers=[];
        $module_dir=Application::getModuleDir($module);
        $prefix= Application::getModulePrefix($module);
        $module=Application::getModuleFillName($module);
        $admin_prefix='';
        if (is_array($prefix)) {
            $admin_prefix=$prefix['admin'] ?? array_shift($prefix);
            $prefix=$prefix['simple'] ?? array_shift($prefix);
        }
        if (Storage::exist($file=MODULES_DIR.'/'.$module_dir.'/resource/config/router.json')) {
            $simple_routers= self::loadModuleJson($module, $file);
            _D()->trace(_T('loading simple route from file %s', $file));
            array_walk($simple_routers, function (&$router) use ($module, $prefix) {
                if (!is_null($prefix)) {
                    $router['visit']=$prefix.$router['visit'];
                }
                $router['visit']='/'.trim($router['visit'], '/');
                $router['role']='simple';
            });
        }
        // 加载后台路由
        if (Storage::exist($file=MODULES_DIR.'/'.$module_dir.'/resource/config/router_admin.json')) {
            $admin_routers= self::loadModuleJson($module, $file);
            _D()->trace(_T('loading admin route from file  %s', $file));
            array_walk($admin_routers, function (&$router) use ($module, $admin_prefix) {
                $prefix= conf('app.admin', '/admin');
                if (!is_null($admin_prefix)) {
                    $prefix = $prefix . $admin_prefix;
                }
                $router['visit']=$prefix.$router['visit'];
                $router['visit']='/'.trim($router['visit'], '/');
                $router['role']='admin';
            });
        }
        self::$routerinfos[$module]=array_merge($admin_routers, $simple_routers);
    }
}
