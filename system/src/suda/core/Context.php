<?php
namespace suda\core;
use suda\core\Application;
use suda\tool\Json;

/**
 * Context 环境
 */
class Context
{
    /**
     * 读取各个模块下config文件的内容
     *
     * @param string $filename
     * @return void
     */
    public static function config(string $filename)
    {
        $module_dirs=Application::getModuleDirs();
        $configs=[];
        foreach ($module_dirs as $module_dir) {
            if (Storage::exist($jsonfile=MODULES_DIR.'/'.$module_dir.'/resource/config/'.$filename.'.json')) {
                $config=Json::loadFile($jsonfile);
                $configs=array_merge($configs, $config);
            }
        }
        return $configs;
    }
}
