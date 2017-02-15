<?php
namespace suda\core;
/**
 * Class Cache
 * 文件缓存
 */
class Cache
{
    public static $cache;
    /**
     * 设置
     * @param string $name 名
     * @param $value 值
     * @param int $expire 过期时间
     * @return bool
     */
    public static function set(string $name, $value, int $expire=0):bool
    {
        $path=CACHE_DIR.'/'.self::nam($name);
        self::$cache[$name]=$value;
        Storage::mkdirs(dirname($path));
        $value=serialize($value);
        return file_put_contents($path, $expire.'|'.$value)!==false;
    }

    /**
     * 获取值
     * @param string $name 名
     * @return mixed|null
     */
    public static function get(string $name,$defalut=null)
    {
        // 有值就获取值
        if (isset(self::$cache[$name])) {
            $value=self::$cache[$name];
            return $value;
        }
        // 没值就在cache文件中查找
        $path=CACHE_DIR.'/'.self::nam($name);
        if (Storage::exist($path)) {
            $value=Storage::get($path);
            $time=explode('|', $value, 2);
            if (time()<intval($time[0]) || intval($time[0])===0) {
                // 未过期则返回 
                return unserialize($time[1]);
            } else {
                // 过期则删除
                self::delete($path);
            }
        }
        // 返回默认值
        return $defalut;
    }

    /**
     * 删除值
     * @param string $name 名
     * @return bool
     */
    public static function delete(string $name) :bool
    {
        return Storage::remove(self::nam($name));
    }
    // 检测
    public static function has(string $name):bool
    {
        return self::get($name)!==null;
    }

    /**
     * 垃圾回收
     */
    public static function gc()
    {
        $files=Storage::readDirFiles($path=CACHE_DIR, '/^(?!\.)/');
        foreach ($files as $file) {
            if (\Config::get('NoCache', 0)) {
                Storage::remove($file);
            } else {
                $value=Storage::get($file);
                $time=explode('|', $value, 2);
                if (intval($time[0])!==0 && intval($time[0])<time()) {
                    Storage::remove($file);
                }
            }
        }
    }
    private static function nam(string $name)
    {
        $str=preg_split('/[.\/]+/', $name, 2, PREG_SPLIT_NO_EMPTY);
        return $str[0].'_'.md5($name);
    }
}
