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

defined('D_START') or define('D_START', microtime(true));
defined('D_MEM') or define('D_MEM', memory_get_usage());
defined('ROOT_PATH') or define('ROOT_PATH', dirname(dirname(dirname(dirname(__DIR__)))));
defined('SYSTEM_DIR') or define('SYSTEM_DIR', dirname(dirname(dirname(__DIR__))));
defined('SYSTEM_RESOURCE') or define('SYSTEM_RESOURCE', SYSTEM_DIR.'/resource');
defined('DEBUG') or define('DEBUG', false);
defined('IS_LINUX') or define('IS_LINUX', DIRECTORY_SEPARATOR ===  '/');
define('SUDA_VERSION', '1.2.15');

require_once __DIR__.'/functions.php';
require_once __DIR__.'/Debug.php';


use suda\archive\SQLQuery;
use suda\tool\Json;
use suda\tool\Value;
use suda\core\exception\ApplicationException;
use suda\exception\JSONException;

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
        // 注册基本常量
        defined('MODULES_DIR') or define('MODULES_DIR', Storage::path(APP_DIR.'/modules'));
        defined('RESOURCE_DIR') or define('RESOURCE_DIR', Storage::path(APP_DIR.'/resource'));
        defined('DATA_DIR') or define('DATA_DIR', Storage::path(APP_DIR.'/data'));
        defined('RUNTIME_DIR') or define('RUNTIME_DIR', Storage::path(DATA_DIR.'/runtime'));
        defined('VIEWS_DIR') or define('VIEWS_DIR', Storage::path(DATA_DIR.'/views'));
        defined('CACHE_DIR') or define('CACHE_DIR', Storage::path(DATA_DIR.'/cache'));
        defined('CONFIG_DIR') or define('CONFIG_DIR', Storage::path(RESOURCE_DIR.'/config'));
        defined('TEMP_DIR') or define('TEMP_DIR', Storage::path(DATA_DIR.'/temp'));
        defined('SHRAE_DIR') or define('SHRAE_DIR', Storage::path(APP_DIR.'/share'));
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
        self::console();
        debug()->timeEnd('init application');
        debug()->time('init router');
        $router->loadModulesRouter();
        debug()->timeEnd('init router');
        debug()->time('run request');
        $router->dispatch();
        debug()->timeEnd('run request');
        debug()->time('before shutdown');
    }

    public static function console()
    {
        $app=Storage::path(APP_DIR);
        self::readManifast(APP_DIR.'/manifast.json');
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
        debug()->trace(__('loading application %s from %s', $name, $app));
        self::$appInstance= $name::getInstance();
        self::$appInstance->init();
    }

    protected static function readManifast(string $manifast)
    {
        debug()->trace(__('reading manifast file'));
        // App不存在
        if (!Storage::exist($manifast)) {
            debug()->trace(__('create base app'));
            Storage::copydir(SYSTEM_RESOURCE.'/app/', APP_DIR);
            Storage::put(APP_DIR.'/modules/default/resource/config/config.json', '{"name":"default"}');
            Config::set('app.init', true);
        }
        Autoloader::addIncludePath(APP_DIR.'/share');
        try {
            // 加载配置
            Config::set('app', Json::loadFile($manifast));
        } catch (JSONException $e) {
            debug()->die(__('parse mainifast error %s', $e->getMessage()));
        }
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
        Cache::gc();
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

    public static function error(int $status,string $type,string $message,?int $code=null,array $params=[]) {
        $render=new class($status,$type,$message,$code,$params) extends Response {
            protected $status,$type,$message,$code,$params;
            public function __construct(int $status,string $type,string $message,?int $code=null,array $params=[]) {
                $this->status =$status;
                $this->type =$type;
                $this->message = $message;
                $this->code = $code;
                $this->params = $params;
            }
            public function onRequest(Request $request)
            {
                $this->state($this->status);
                $page=$this->page('suda:error404', ['error_type'=> $this->type ,'error_message'=> $this->message]);
                if (!is_null($this->code)){
                    $page->set('error_code',$this->code);
                }
                if (is_array($this->params)){
                    $page->assign($this->params);
                }
                $page->render();
            }
        };
        $render->onRequest(Request::getInstance());
    }
}
