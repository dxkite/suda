<?php
namespace suda\core;

class ApplicationManager
{
    public static $instance=null;
    public function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance=new ApplicationManager();
        }
        return self::$instance;
    }
    
    public function run(string $app)
    {

    }
}
