<?php
namespace suda\core;
use Exception;
class Application
{
    protected $path;
    public function Application(string $app){
        $this->path=$app;
        define('DATA_DIR',)
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
