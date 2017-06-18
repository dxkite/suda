## Cache 类
由于访问数据库的效率远远低于访问文件的效率，所以我添加了一个文件缓存类，
你可以把常用的数据和更改很少的数据查询数据库以后缓存到文件里面，用来加快页面加载速度。

### 可用方法

| 方法名 | 说明|
|-------|-----|
| public static function set(string $name, $value, int $expire=null):bool | 设置缓存，如果未设置expire则缓存默认保存1天 |
| public static function get(string $name, $defalut=null) | 获取缓存，如果获取失败返回default的内容|
| public static function delete(string $name) :bool | 删除一个缓存|
|public static function has(string $name):bool| 查询是否存在缓存|
|public static function gc()| 删除过期的缓存，由框架自动叼用|


### 使用例子
```php
public static function cacheAll()
{
    // 判断缓存是否存在
    if (Cache::has('options') && !conf('debug',false)) {
        // 调用缓存
        self::$values=Cache::get('options', []);
    } else {
        // 查询数据库
        self::$values=(new OptionDAO)->listAll();
        // 缓存数据
        Cache::set('options', self::$values);
    }
}
```