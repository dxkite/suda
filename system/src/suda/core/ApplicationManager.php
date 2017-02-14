<?php
namespace suda\core;

use suda\tools\Json;

class ApplicationManager
{
    public static $instance=null;
    public $app=null;
    

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
        //$this->app=new Application($app);
    }

    protected function readManifast(string $manifast)
    {
        // App不存在
        if (!Storage::exist($manifast)) {
            var_dump(Storage::copydir(SYS_RES.'/app_template/', APP_PATH));
        }
    }
}
