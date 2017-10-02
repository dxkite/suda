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
define('SUDA_VERSION', '1.2.9');

require_once __DIR__.'/Debug.php';
require_once __DIR__.'/functions.php';

use suda\archive\SQLQuery;
use suda\tool\Json;
use suda\tool\Value;
use suda\core\exception\ApplicationException;

class System
{
    public static $app_instance=null;
    public static $application_class;

    public static function init()
    {
        class_alias('suda\\core\\System', 'System');
        register_shutdown_function('suda\\core\\System::onShutdown');
        set_error_handler('suda\\core\\System::uncaughtError');
        set_exception_handler('suda\\core\\System::uncaughtException');
        Debug::beforeSystemRun();
        Locale::path(SYSTEM_RESOURCE.'/locales');
        debug()->trace('system init');
        Hook::exec('system:init');
    }
 
    public static function getApplication(){
        return self::$app_instance;
    }
    
    public static function run(string $app)
    {
        debug()->time('init application');
        self::console($app);
        debug()->timeEnd('init application');
        debug()->time('run request');
        Router::getInstance()->dispatch();
        debug()->timeEnd('run request');
        debug()->time('before shutdown');
    }

    public static function console(string $app)
    {
        // 加载配置
        $app=Storage::path($app);
        self::readManifast($app.'/manifast.json');
        $name=Autoloader::realName(self::$application_class);
        debug()->trace(__('loading application %s from %s', $name, $app));
        self::$app_instance=new $name($app);
        if (self::$app_instance instanceof Application) {
            // 设置语言包库
            Locale::path(Storage::path($app.'/resource/locales/'));
            Hook::listen('Router:dispatch::before', [self::$app_instance, 'onRequest']);
            Hook::listen('system:shutdown', [self::$app_instance, 'onShutdown']);
            Hook::listen('system:uncaughtException', [self::$app_instance, 'uncaughtException']);
            Hook::listen('system:uncaughtError', [self::$app_instance, 'uncaughtError']);
        } else {
            throw new ApplicationException(__('unsupport base application class %s', self::$application_class));
        }
    }

    protected static function readManifast(string $manifast)
    {
        debug()->trace(__('reading manifast file'));
        // App不存在
        if (!Storage::exist($manifast)) {
            debug()->trace(__('create base app'));
            Storage::copydir(SYSTEM_RESOURCE.'/app/', APP_DIR);
            Storage::put(APP_DIR.'/modules/default/resource/config/config.json', '{"name":"default"}');
            Config::set('app.init',true);
        }
        Autoloader::addIncludePath(APP_DIR.'/share');
        // 设置配置
        Config::set('app', Json::loadFile($manifast));

        // 载入配置前设置配置
        Hook::exec('core:loadManifast');
        // 默认应用控制器
        self::$application_class=Config::get('app.application', 'suda\\core\\Application');
    }


    public static function onShutdown()
    {
        debug()->timeEnd('before shutdown');
        debug()->time('shutdown');
        // 忽略用户停止
        ignore_user_abort(true);
        // 如果正常连接则设置未来得及发送的Cookie
        if (connection_status() == CONNECTION_NORMAL) {
            Cookie::sendCookies();
            Hook::exec('system:shutdown::before');
        }
        Cache::gc();
        Hook::exec('system:shutdown');
        debug()->trace('connection status '. ['normal','aborted','timeout'][connection_status()]);
        debug()->trace('include paths '.json_encode(Autoloader::getIncludePath()));
        debug()->trace('runinfo', self::getRunInfo());
        debug()->trace('system shutdown');
        debug()->timeEnd('shutdown');
        Debug::phpShutdown();
    }

    public static function uncaughtException($exception)
    {
        if (!$exception instanceof Exception) {
            $exception=new Exception($exception);
        }
        if (Hook::execIf('system:displayException', [$exception], false)) {
            Debug::displayException($exception);
        }
    }

    // 错误托管
    public static function uncaughtError($errno, $errstr, $errfile, $errline)
    {
        self::uncaughtException(new \ErrorException($errstr, 0, $errno, $errfile, $errline));
    }

    public static function getRunInfo()
    {
        $info=Debug::getInfo();
        $info=array_merge($info, SQLQuery::getRunInfo());
        return $info;
    }
}
