<?php
namespace suda\core;

use suda\tool\Json;
use suda\tool\Value;

class ApplicationManager
{
    public static $instance=null;
    public $app=null;
    public $appliaction;
    
    public function getApplication() {
        return $app;
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance=new ApplicationManager();
        }
        return self::$instance;
    }

    public function run(string $app)
    {
        self::console($app);
        Router::getInstance()->dispatch();
    }

    public function console(string $app)
    {
        // 加载配置
        $this->readManifast($app.'/manifast.json');
        $name=Autoloader::realName($this->appliaction);
        $this->app=new $name($app);
        if ($this->app instanceof Application) {
            Hook::listen('Router:dispatch::before', [$this->app, 'onRequest']);
            Hook::listen('system:shutdown', [$this->app, 'onShutdown']);
            Hook::listen('system:uncaughtException', [$this->app, 'uncaughtException']);
            Hook::listen('system:uncaughtError', [$this->app, 'uncaughtError']);
        }else{
            throw new UnsupportApplication('unsupport application core:'.$this->appliaction);
        }
        
    }

    protected function readManifast(string $manifast)
    {
        // App不存在
        if (!Storage::exist($manifast)) {
            Storage::copydir(SYS_RES.'/app_template/', APP_DIR);
            $content=str_replace('__SYS_DIR__',SYS_DIR,Storage::get(APP_DIR.'/console'));
            Storage::put(APP_DIR.'/console',$content);
        }
        Autoloader::addIncludePath(APP_DIR.'/share');
        // 设置配置
        Config::set('app', Json::loadFile($manifast));
        // 载入配置前设置配置
        Hook::exec('core:loadManifast');
        // 默认应用控制器
        $this->appliaction=Config::get('app.application', 'suda\\core\\Application');
    }
}
