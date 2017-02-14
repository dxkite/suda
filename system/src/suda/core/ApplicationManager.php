<?php
namespace suda\core;
use suda\tool\Json;
use suda\tool\Value;

class ApplicationManager
{
    public static $instance=null;
    public $app=null;
    public $appliaction;

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance=new ApplicationManager();
        }
        return self::$instance;
    }

    public function run(string $app)
    {
        $this->readManifast($app.'/manifast.json');
        $this->app=new $this->appliaction($app);
        Router::getInstance()->dispatch();
    }

    protected function readManifast(string $manifast)
    {
        // App不存在
        if (!Storage::exist($manifast)) {
            Storage::copydir(SYS_RES.'/app_template/', APP_DIR);
        }
        // 设置配置
        Config::set('app',Json::loadFile($manifast));
        // 载入配置前设置配置
        Hook::exec('core:loadManifast');
        // 默认应用控制器
        $this->appliaction=Config::get('app.application','suda\\core\\Application');

    }
}
