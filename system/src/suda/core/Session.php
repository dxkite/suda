<?php
namespace suda\core;

class Session
{
    public static $fname=[
        'id'=>'id',
        'cacheExpire'=>'cache_expire',
        'cacheLimiter'=>'cache_limiter',
        'setCookieParams'=>'set_cookie_params',
    ];
    public static function start()
    {
        $path=DATE_DIR.'/'.conf('session.save_path','session');
        Storage::mkdirs($path);
        session_save_path($path);
        session_name(conf('session.name', 'suda_session'));
        session_cache_limiter(conf('session.limiter', 'private'));
        session_cache_expire(conf('session.expire', '60'));
        session_start();
    }
    public static function set(string $name, $value)
    {
        $_SESSION[$name]=$value;
        return isset($_SESSION[$name]);
    }
    public static function get(string $name='', $default=null)
    {
        if ($name) {
            return isset($_SESSION[$name])?$_SESSION[$name]:$default;
        } else {
            return $_SESSION;
        }
    }
    public static function has(string $name)
    {
        return isset($_SESSION[$name]);
    }
    public static function __callStatic(string $name, array $params)
    {
        if (array_key_exists($name, self::$fname)) {
            return call_user_func_array('session_'.self::$fname[$name], $params);
        }
    }
    public static function destroy()
    {
        session_unset();
    }
    public static function regenerate(bool $delete=false) :bool
    {
        return session_regenerate_id($delete);
    }
}
