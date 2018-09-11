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

require_once __DIR__.'/functions.php';
require_once __DIR__.'/Debug.php';


use suda\archive\SQLQuery;
use suda\tool\Json;
use suda\tool\Value;
use suda\core\exception\ApplicationException;

/**
 * 系统类，处理系统报错函数以及程序加载
 */
class System
{
    protected static $appInstance=null;
    protected static $applicationClass=null;
    const APP_CACHE='app.cache';

    public static function init()
    {
        class_alias('suda\\core\\System', 'System');
        // 错误处理
        register_shutdown_function('suda\\core\\System::onShutdown');
        set_error_handler('suda\\core\\System::uncaughtError');
        set_exception_handler('suda\\core\\System::uncaughtException');
        if (!DEBUG) {
            ini_set('display_errors', 'Off');
        }

        defined('RUNTIME_DIR') or define('RUNTIME_DIR', Storage::path(DATA_DIR.'/runtime'));
        defined('VIEWS_DIR') or define('VIEWS_DIR', Storage::path(DATA_DIR.'/views'));
        defined('CACHE_DIR') or define('CACHE_DIR', Storage::path(DATA_DIR.'/cache'));
        defined('TEMP_DIR') or define('TEMP_DIR', Storage::path(DATA_DIR.'/temp'));

        Debug::beforeSystemRun();
        Locale::path(SYSTEM_RESOURCE.'/locales');
        debug()->trace('system init');
        Hook::exec('system:init');
    }
 
    public static function getAppInstance()
    {
        return self::$appInstance;
    }
    
    public static function getAppClassName()
    {
        if (is_null(self::$applicationClass)) {
            self::$applicationClass = class_name(Config::get('app.application', 'suda.core.Application'));
        }
        return self::$applicationClass;
    }

    public static function run()
    {
        debug()->time('init application');
        $router=Router::getInstance();
        static::initApplication();
        debug()->timeEnd('init application');
        debug()->time('init router');
        $router->loadModulesRouter();
        debug()->timeEnd('init router');
        debug()->time('run request');
        $router->dispatch();
        debug()->timeEnd('run request');
        debug()->time('before shutdown');
    }

    public static function initApplication()
    {
        defined('MODULES_DIR') or define('MODULES_DIR', Storage::path(APP_DIR.'/modules'));
        defined('RESOURCE_DIR') or define('RESOURCE_DIR', Storage::path(APP_DIR.'/resource'));
        defined('DATA_DIR') or define('DATA_DIR', Storage::path(APP_DIR.'/data'));
        defined('SHRAE_DIR') or define('SHRAE_DIR', Storage::path(APP_DIR.'/share'));
        defined('CONFIG_DIR') or define('CONFIG_DIR', Storage::path(RESOURCE_DIR.'/config'));

        Storage::path(APP_DIR);
        // 检测 app vendor
        if (storage()->exist($vendor = APP_DIR.'/vendor/autoload.php')) {
            Autoloader::import($vendor);
        }
        self::readManifast();
        $name=Autoloader::realName(self::$applicationClass);
        // 加载共享库
        foreach (Config::get('app.import', [''=>SHRAE_DIR]) as $namespace=>$path) {
            if (Storage::isDir($srcPath=APP_DIR.DIRECTORY_SEPARATOR.$path)) {
                Autoloader::addIncludePath($srcPath, $namespace);
            } elseif (Storage::isFile($importPath=APP_DIR.DIRECTORY_SEPARATOR.$path)) {
                Autoloader::import($importPath);
            }
        }
        Config::set('app.application', $name);
        debug()->trace(__('loading application %s from %s', $name, APP_DIR));
        self::$appInstance= $name::getInstance();
        self::$appInstance->init();
    }

    public static function createApplication(string $path)
    {
        Storage::copydir(SYSTEM_RESOURCE.'/app/', $path);
    }

    protected static function readManifast()
    {
        debug()->trace(__('reading manifast file'));
        $path = APP_DIR.DIRECTORY_SEPARATOR.'manifast.json';
        $manifast  = [];
        if (!Config::resolve($path)) {
            debug()->trace(__('create base app'));
            static::createApplication(APP_DIR);
            Config::set('app.init', true);
        }

        try {
            $manifast = Config::load($path);
        } catch (\Exception $e) {
            $message =__('Load application mainifast: parse mainifast error: %s', $e->getMessage());
            debug()->error($message);
            suda_panic('Kernal Panic', $message);
        }
        
        Autoloader::addIncludePath(APP_DIR.'/share');
        Config::set('app', $manifast);
        // 载入配置前设置配置
        Hook::exec('core:loadManifast');
        // 默认应用控制器
        self::$applicationClass=self::getAppClassName();
    }

    public static function onShutdown()
    {
        // 忽略用户停止脚本
        ignore_user_abort(true);
        debug()->timeEnd('before shutdown');
        debug()->time('shutdown');
        // 发送Cookie
        if (connection_status() == CONNECTION_NORMAL) {
            Hook::exec('system:shutdown::before');
        }
        // 停止响应输出
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
        Hook::exec('system:shutdown');
        debug()->trace('connection status '. ['normal','aborted','timeout'][connection_status()]);
        // debug()->trace('include paths '.json_encode(Autoloader::getIncludePath()));
        // debug()->trace('runinfo', self::getRunInfo());
        debug()->trace('system shutdown');
        debug()->timeEnd('shutdown');
        Debug::phpShutdown();
    }

    public static function uncaughtException($exception)
    {
        Config::set('exception', true);
        if (!Hook::execIf('system:displayException', [$exception], true)) {
            if (!$exception instanceof Exception) {
                $exception=new Exception($exception);
            }
            Debug::displayException($exception);
        }
    }

    // 错误托管
    public static function uncaughtError($errno, $errstr, $errfile, $errline)
    {
        self::uncaughtException(new \ErrorException($errstr, 0, $errno, $errfile, $errline));
    }

    public static function error(int $status, string $type, string $message, ?int $code=null, array $params=[])
    {
        $render=new class($status, $type, $message, $code, $params) extends Response {
            protected $status;
            protected $type;
            protected $message;
            protected $code;
            protected $params;
            public function __construct(int $status, string $type, string $message, ?int $code=null, array $params=[])
            {
                $this->status =$status;
                $this->type =$type;
                $this->message = $message;
                $this->code = $code;
                $this->params = $params;
            }
            public function onRequest(Request $request)
            {
                $this->state($this->status);
                $page=$this->page('suda:error', ['error_type'=> $this->type ,'error_message'=> $this->message]);
                if (!is_null($this->code)) {
                    $page->set('error_code', $this->code);
                }
                if (is_array($this->params)) {
                    $page->assign($this->params);
                }
                $page->render();
            }
        };
        $render->onRequest(Request::getInstance());
    }
}
