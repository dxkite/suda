# 文档清单

> **注：** 文档由程序自动生成

## 函数列表 


### [mime](functions/mime.md)
 根据文件类型获取MIME描述
其中文本硬编码为UTF-8
 

### [__](functions/__.md)
 语言翻译，I18N支持，依赖locales文件夹下的文件

## 使用例子

```php
 echo __('text is %d',1);
``` 

### [debug](functions/debug.md)
 获取debug对象
 

### [conf](functions/conf.md)
 获取配置信息
 

### [use_namespace](functions/use_namespace.md)
 使用命名空间
 

### [u](functions/u.md)
 根据路由名获取URL
如果第一个参数为字符串，则将字符串作为路由名称，第二个参数作为路由的值获取组合后的路由
如果第一个参数为数组，则获取正在运行的路由的URL，参数使用第一个参数
 

### [assets](functions/assets.md)
 根据模块名称获取资源URL
 

### [import](functions/import.md)
 导入PHP文件
 

### [init_resource](functions/init_resource.md)
 初始化资源
 

### [app](functions/app.md)
 获取当运行的APP单例对象
 

### [router](functions/router.md)
 获取当运行的路由单例对象
 

### [request](functions/request.md)
 获取当运行的请求的单例对象
 

### [hook](functions/hook.md)
 获取当运行的请求的单例对象
 

### [cookie](functions/cookie.md)
 获取当运行的请求的单例对象
 

### [cache](functions/cache.md)
 获取一个缓存对象
 

### [storage](functions/storage.md)
 获取一个储存对象
 

### [config](functions/config.md)
 获取一个配置对象
 

### [cmd](functions/cmd.md)
 新建一个命令对象，命令对象可以是一个字符串或者一个数组，也可以是一个匿名包对象
还可以是一个标准可调用的格式的字符串

### 静态方法
```
类名::方法名
```

### 动态方法

```
类名->方法名
```
 

### [class_name](functions/class_name.md)
 获取类名，将JAVA包式的类名转化为 PHP的标准类名
 

### [table](functions/table.md)
 获取数据表对象，该对象需要在module.json文件中注册过
如：
```json
{
   "table":{
       "user":"classNameOfUserTable"
    }
}
```
 




## 类列表


### [suda\core\Autoloader](classes/suda/core/Autoloader.md)
 

### [suda\core\System](classes/suda/core/System.md)
 

### [suda\core\Debug](classes/suda/core/Debug.md)
 

### [suda\core\Request](classes/suda/core/Request.md)
 

### [suda\tool\Value](classes/suda/tool/Value.md)
Class Value
@package Core 

### [suda\core\Storage](classes/suda/core/Storage.md)
 

### [suda\core\Config](classes/suda/core/Config.md)
 

### [suda\tool\ArrayHelper](classes/suda/tool/ArrayHelper.md)
数组操纵，
设置值，
获取值
导出成文件 

### [system](classes/system.md)
 

### [suda\core\Hook](classes/suda/core/Hook.md)
 

### [suda\core\Locale](classes/suda/core/Locale.md)
I18N 国际化支持
语言控制文件 

### [suda\tool\Json](classes/suda/tool/Json.md)
 

### [suda\core\Application](classes/suda/core/Application.md)
 

### [suda\template\Manager](classes/suda/template/Manager.md)
模板管理类 

### [suda\template\compiler\suda\Compiler](classes/suda/template/compiler/suda/Compiler.md)
Suda 模板编译器 

### [suda\tool\Command](classes/suda/tool/Command.md)
 

### [autoloader](classes/autoloader.md)
 

### [doc\Summary](classes/doc/Summary.md)
反射导出注释文档 

### [suda\archive\creator\Field](classes/suda/archive/creator/Field.md)
 

### [suda\archive\creator\InputValue](classes/suda/archive/creator/InputValue.md)
 

### [suda\archive\creator\Table](classes/suda/archive/creator/Table.md)
 

### [suda\archive\SQLQuery](classes/suda/archive/SQLQuery.md)
 

### [suda\archive\Table](classes/suda/archive/Table.md)
数据表抽象对象
用于提供对数据表的操作
 

### [suda\archive\TableInstance](classes/suda/archive/TableInstance.md)
 

### [suda\core\Cache](classes/suda/core/Cache.md)
Class Cache
文件缓存 

### [suda\core\Cookie](classes/suda/core/Cookie.md)
Class Cookie
用于获取Cookie 

### [suda\core\Exception](classes/suda/core/Exception.md)
 

### [suda\core\Query](classes/suda/core/Query.md)
 

### [suda\core\Response](classes/suda/core/Response.md)
 

### [suda\core\route\Mapping](classes/suda/core/route/Mapping.md)
 

### [suda\core\Router](classes/suda/core/Router.md)
 

### [suda\core\Session](classes/suda/core/Session.md)
 

### [suda\exception\ApplicationException](classes/suda/exception/ApplicationException.md)
 

### [suda\exception\ArchiveException](classes/suda/exception/ArchiveException.md)
 

### [suda\exception\CommandException](classes/suda/exception/CommandException.md)
 

### [suda\exception\JSONException](classes/suda/exception/JSONException.md)
 

### [suda\exception\KernelException](classes/suda/exception/KernelException.md)
 

### [suda\exception\MailException](classes/suda/exception/MailException.md)
 

### [suda\exception\SQLException](classes/suda/exception/SQLException.md)
 

### [suda\exception\TableException](classes/suda/exception/TableException.md)
 

### [suda\mail\Factory](classes/suda/mail/Factory.md)
 

### [suda\mail\message\Message](classes/suda/mail/message/Message.md)
 

### [suda\mail\message\HTMLMessage](classes/suda/mail/message/HTMLMessage.md)
 

### [suda\mail\sender\MailSender](classes/suda/mail/sender/MailSender.md)
 

### [suda\mail\sender\StmpSender](classes/suda/mail/sender/StmpSender.md)
 

### [suda\template\compiler\suda\Template](classes/suda/template/compiler/suda/Template.md)
 

### [suda\template\compiler\suda\TemplateInfo](classes/suda/template/compiler/suda/TemplateInfo.md)
 

### [suda\tool\ArrayValue](classes/suda/tool/ArrayValue.md)
 

### [suda\tool\CookieSetter](classes/suda/tool/CookieSetter.md)
 

### [suda\tool\Docme](classes/suda/tool/Docme.md)
Doc Me
create a doc for class or function
use markdown style to doc this. 

### [suda\tool\EchoValue](classes/suda/tool/EchoValue.md)
 

### [suda\tool\Pinyin](classes/suda/tool/Pinyin.md)
 

### [suda\tool\ZipHelper](classes/suda/tool/ZipHelper.md)
 

### [doc\FunctionExport](classes/doc/FunctionExport.md)
 

### [doc\ClassExport](classes/doc/ClassExport.md)
 
