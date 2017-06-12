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

namespace dxkite\suda;

use suda\core\Application;
use suda\core\Storage;
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
                if ($module===$mname || !Application::checkModuleExist($mname)) {
                    unset($manifast['modules'][$index]);
                }
            }
            _D()->trace('module dead', $module);
            return Json::saveFile($manifast_file, $manifast);
        } else {
            if (!in_array($module, $manifast['modules'])) {
                $manifast['modules'][]=$module;
            }
            _D()->trace('module live', $module);
            return Json::saveFile($manifast_file, $manifast);
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

    public static function createModule(string $name, string $version,string $homepage, string $locale, string $namespace, string $require, string $authors, string $discription)
    {
        $config['name']=$name;
        $config['homepage']=$homepage;
        $config['version']=$version;
        $config['locale']=$locale;
        $config['namespace']=$namespace;
        $config['discription']=$discription;
        $requires=explode(',', trim($require, ','));
        foreach ($requires as $require) {
            if (preg_match('/^(.+?):(.+)$/', $require, $match)) {
                $config['require'][$match[1]]=$match[2];
            }
        }
        $authors=explode(',', trim($authors, ','));
        foreach ($authors as $author) {
            if (preg_match('/(.+?)<(.+?)>/', $author, $match)) {
                $add['name']=$match[1];
                $add['email']=$match[2];
                $config['authors'][]=$add;
            }
        }
        $dirname=preg_replace('/[\\\\\/]+/','-',$name).'-'.$version;
        $path=MODULES_DIR.'/'.$dirname;
        Storage::path($path);
        return Json::saveFile($path.'/module.json', $config);
    }
}
