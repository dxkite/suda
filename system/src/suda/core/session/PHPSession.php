<?php
/**
 * Suda FrameWork
 *
 * An open source application development framework for PHP 7.2.0 or newer
 * 
 * Copyright (c)  2017-2018 DXkite
 *
 * @category   PHP FrameWork
 * @package    Suda
 * @copyright  Copyright (c) DXkite
 * @license    MIT
 * @link       https://github.com/DXkite/suda
 * @version    since 2.0
 */

namespace suda\core\session;

use suda\core\Storage;

/**
 * 会话操纵类
 * 控制PHP全局会话，
 */
class PHPSession implements Session
{
    protected static $instance;
    
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance=new  self;
        }
        return self::$instance;
    }
    
    protected function __construct()
    {
        if (session_status()==PHP_SESSION_NONE) {
            $path=DATA_DIR.'/session';
            Storage::mkdirs($path);
            session_save_path($path);
            session_name(conf('session.name', 'session'));
            session_cache_limiter(conf('session.limiter', 'private'));
            session_cache_expire(conf('session.expire', 0));
            session_start();
        }
    }

    public function set(string $name, $value)
    {
        $_SESSION[$name]=$value;
        return isset($_SESSION[$name]);
    }

    public function get(string $name='', $default=null)
    {
        if ($name) {
            return isset($_SESSION[$name])?$_SESSION[$name]:$default;
        } else {
            return $_SESSION;
        }
    }

    public function delete(?string $name=null)
    {
        if (is_null($name)) {
            session_unset();
        } else {
            unset($_SESSION[$name]);
        }
    }

    public function has(string $name)
    {
        return isset($_SESSION[$name]);
    }

    public function destroy()
    {
        session_unset();
        session_destroy();
    }
}

// 初始化
PHPSession::getInstance();
