# Request 响应基类（抽象类）
该类用于响应来自浏览器的请求，是每条路由必须继承的基类。含抽象方法 `onRequest` 在接受到请求后调用。
响应提供了四种页面输出方式：模板渲染、JSON输出、无缓存输出和任意输出。

## 提供的方法
| 方法 |说明|
|-----|-----|
| public function onPreTest($test_data):bool | 前置测试，当页面前被调用，用于前置测试访问是否合格，默认返回`true` 其中`test_data`为路由信息|
| public function onPreTestError($test_data) | 当前置测试返回false的时候被调用，显示错误原因 |
| abstract public function onRequest(Request $request) | 抽象方法，当前置测试返回true的时候被调用，响应正确页面|
| public static function state(int $state) | 用于设置页面反馈的HTTP状态 |
| public function type(string $type) | 用户设置HTTP返回的内容类型，由系统定义mime信息 |
| public function noCache() | 设置无缓存控制 |
| public function json($values) | 设置页面结果为输出JSON数据、参数使用可以被转换为JSON的数据 |
| public function display(string $template, array $values=[]) | 设置渲染页面结果为HTML，template为使用的模板，values为模板将要使用的变量 |
| public function displayFile(string $path, array $values=[]) | 设置渲染页面结果为HTML，path为已经编译的模板文件|
| public static function etag(string $etag) | 设置页面的Etag,默认为MD5；如果Etag未变，自动返回304 |
| public static function close() | 返回HTTP头：关闭连接 |
| public static function obStart() | 开启OB缓存 |
| public function obEnd() | 关闭OB缓存 |
| public static function mime(string $name='') | 设置页面 MIME 类型 |
| 