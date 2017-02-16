# 工具使用方法
## `app/console`
## router 路由自动生成
用以自动生成路由，包括模板，类，和路由注册。
### 使用
```
Usage: router [-a] [-o]  [-m POST,GET..] [-t name] -u url -c class

    -a create URL as admin
    -m request method,use `,` to split 
    -u the request url, use {name:type} to assign args
    -c the called class when request
    -o disavaliable ob cache
    -t the tag name of url
```
### 示例
```
dxkite@atd3.cn:/workspace>php app\console --router user_admin -u /user/admin -c user\Admin@default
created response:user\Admin@default
```
该命令会生成如下文件

#### `Test.php`
```php
<?php
namespace cn\atd3\response;

use suda\core\Request;
use suda\core\Session;
use suda\core\Cookie;

// Auto generate response class
class Test extends \suda\core\Response {
    public function onRequest(Request $request){
        // auto create params getter ...
		$param=$request->get()->param("hello!");

        $this->display('default:test',['helloworld'=>'Hello,World!']);
    }
}
```
和模板文件
#### `test.tpl.html`
```html
<html>
    <head>
        <title>{{ $v->helloworld }}</title>
    </head>
    <body>
        <div> {{ _T($v->helloworld) }}@/test/{param:string} </div>
    </body>
</html>
```
通过浏览器访问url `/test/somestring` 来运行本类的`onRequest`方法

## call 系统调用工具
可以调用公用函数和类方法。
### 使用
```
Usage: app\console --call caller arg1 arg2...
Format:
        call class method: namespace\class#method arg1 arg2...
        call class static method: namespace\class::method arg1 arg2...
        call function: function arg1 arg2...
```

### 示例
```
dxkite@atd3.cn:/workspace>php system/call conf app.namespace
call conf(app.namespace)
-----------------------------------
# Function echo
-----------------------------------
# return value
-----------------------------------
string(7) "cn\atd3"
```

## db 数据库工具
根据应用config.json配置的数据库信息，用来导出生成备份数据库
### 使用
```
Usage: app\console --db -bkifgrosp 

  -b backup database
  -k set install sql file keep tables
  -i create install database file
  -f import database from  php file
  ---------------------------------------
  -g generate dto file
    -r raw dto directory 
    -o output directory
    -s set output sql path
    -p set output php path
```

-------------
@DXkite