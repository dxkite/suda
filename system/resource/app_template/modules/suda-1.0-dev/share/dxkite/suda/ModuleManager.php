<?php
namespace dxkite\suda;

use suda\core\Application;
use suda\tool\Json;

class ModuleManager
{
    const MODULE_OFF=0;
    const MODULE_ON=1;

    public static function setModuleStatu(string $module, int $status)
    {
        $manifast_file=APP_DIR.'/manifast.json';
        $manifast=Json::parseFile($manifast_file);
        if ($status === self::MODULE_OFF) {
            $module=Application::getModuleFullName($module);
            foreach ($manifast['modules'] as $index => $mname) {
                $mname=Application::getModuleFullName($mname);
                if ($module===$mname) {
                    unset($manifast['modules'][$index]);
                }
            }
            _D()->trace('module dead',$module);
            return Json::saveFile($manifast_file,$manifast);
        }
        else{
            if (!in_array($module,$manifast['modules'])){
                $manifast['modules'][]=$module;
            }
            _D()->trace('module live',$module);
            return Json::saveFile($manifast_file,$manifast);
        }
    }
    
    public static function getModulesInfo()
    {
        $module_use=Application::getLiveModules();
        $all=Application::getModulesInfo();
        foreach ($all as $index=>$module) {
            if (in_array($index, $module_use)) {
                $all[$index]['on']=true;
            } else {
                $all[$index]['on']=false;
            }
        }
        return $all;
    }
}
