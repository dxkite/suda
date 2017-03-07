# Request 类

Request类是一个不可继承的单列类，封装了大多数必用的接口，如 POST,GET的 值的获取。

| 方法名 | 说明 |
|-------|------|
| public static function getInstance() | 获取实例化的对象 |
| public static function json()     | 获取提交的JSON |
| public static function input()    | 获取提交的内容  |
| public static function method()   | 获取请求的接口 |
| public static function url()      | 获取请求的URL（不包含查询字符）|
| public static function set(string $name, $value) | 设置GET的值 |
| public static function get(string $name='') |获取GET的值 |
| public static function post(string $name='') | 获取POST的值 |
| public static function ip() | 获取IP地址 |
| public static function isPost() | 判断请求是否为POST |
| public static function hasGet() | 判断GET中是否有值 |
| public static function isJson() | 判断提交内容是否为JSON|
| public static function getHeader(string $name,string $default=null)| 获取请求中的自定义对象 |