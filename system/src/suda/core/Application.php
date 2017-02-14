<?php
namespace suda\core;
use Exception;
class Application
{
    protected $path;
    public function Application(string $app){
        $this->path=$app;
        // 基本常量
        defined('MODULES_DIR') or define('MODULES_DIR', APP_DIR.'/modules');
        defined('RESOURCE_DIR') or define('RESOURCE_DIR', APP_DIR.'/resource');
        defined('DATA_DIR') or define('DATA_DIR', APP_DIR.'/data');
        defined('VIEWS_DIR') or define('VIEWS_DIR',DATA_DIR.'/views');
        defined('CACHE_DIR') or define('CACHE_DIR', RESOURCE_DIR.'/cache');
        defined('CONFIG_DIR') or define('CONFIG_DIR', RESOURCE_DIR.'/config');
        defined('TEMP_DIR') or define('TEMP_DIR', RESOURCE_DIR.'/config');

        // 设置PHP属性
        set_time_limit(Config::get('timelimit', 0));
        // 设置时区
        date_default_timezone_set(Config::get('timezone', 'PRC'));
    }

    public function onRequest(Request $request){
        
    }
    public function onShutdown(){

    }

    public function uncaughtException(Exception $e){

    }
    public function uncaughtError(){

    }
}
