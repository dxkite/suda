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
 * @version    since 1.2.4
 */
namespace suda\core;

use suda\tool\Json;
use suda\tool\Value;
use suda\core\exception\ApplicationException;

class ApplicationManager
{
    public static $instance=null;
    public $app=null;
    public $appliaction;
    
    public function getApplication()
    {
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
        $app=Storage::path($app);
        $this->readManifast($app.'/manifast.json');
        $name=Autoloader::realName($this->appliaction);
        _D()->trace(__('loading application %s from %s', $name, $app));
        $this->app=new $name($app);
        if ($this->app instanceof Application) {
            // 设置语言包库
            Locale::path(Storage::path($app.'/resource/locales/'));
            Hook::listen('Router:dispatch::before', [$this->app, 'onRequest']);
            Hook::listen('system:shutdown', [$this->app, 'onShutdown']);
            Hook::listen('system:uncaughtException', [$this->app, 'uncaughtException']);
            Hook::listen('system:uncaughtError', [$this->app, 'uncaughtError']);
        } else {
            throw new ApplicationException(__('unsupport base application class %s', $this->appliaction));
        }
    }

    protected function readManifast(string $manifast)
    {
        _D()->trace(__('reading manifast file'));
        // App不存在
        if (!Storage::exist($manifast)) {
            _D()->trace(__('create base app'));
            Storage::copydir(SYSTEM_RESOURCE.'/app_template/', APP_DIR);
            Storage::put(APP_DIR.'/modules/default/resource/config/config.json', '{"name":"default"}');
        }
        Autoloader::addIncludePath(APP_DIR.'/share');
        // 设置配置
        Config::set('app', Json::loadFile($manifast));
        // 开发状态覆盖
        if (defined('DEBUG')) {
            Config::set('app.debug', DEBUG);
            Config::set('debug', DEBUG);
        }
        // 载入配置前设置配置
        Hook::exec('core:loadManifast');
        // 默认应用控制器
        $this->appliaction=Config::get('app.application', 'suda\\core\\Application');
    }
}
