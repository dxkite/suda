<?php
namespace suda\template;

use Storage;
use suda\tool\Json;

class Language
{
    private static $langs=[];

    public static function assign(array $lang)
    {
        return self::$langs=array_merge(self::$langs, $lang);
    }
    public static function load(string $path)
    {
        $lang=Json::loadFile($path);
        return self::assign($lang);
    }
    public static function trans(string $messageid)
    {
        $message=$messageid;
        if (isset(self::$langs[$messageid])) {
            $message=self::$langs[$messageid];
        }
        $args=func_get_args();
        if (count($args)>1) {
            $args[0]=$message;
            return call_user_func_array('sprintf', $args);
        }
        return $message;
    }
}
