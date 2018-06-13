# 文档清单

> **注：** 文档由程序自动生成

- suda 1.2.15 
- 2018-06-13 13:24:16

## 函数列表 

| 函数名 | 说明 |
|------|-----|  
| [mime](functions/mime.md) |  根据文件类型获取MIME描述  |
| [__](functions/__.md) |  语言翻译，I18N支持，依赖locales文件夹下的文件  |
| [debug](functions/debug.md) |  获取debug对象  |
| [conf](functions/conf.md) |  获取配置信息  |
| [use_namespace](functions/use_namespace.md) |  使用命名空间  |
| [u](functions/u.md) |  根据路由名获取URL  |
| [assets](functions/assets.md) |  根据模块名称获取资源URL  |
| [import](functions/import.md) |  导入PHP文件  |
| [init_resource](functions/init_resource.md) |  初始化资源  |
| [app](functions/app.md) |  获取当运行的APP单例对象  |
| [router](functions/router.md) |  获取当运行的路由单例对象  |
| [request](functions/request.md) |  获取当运行的请求的单例对象  |
| [hook](functions/hook.md) |  获取当系统钩子对象  |
| [cookie](functions/cookie.md) |  获取Cookie对象  |
| [cache](functions/cache.md) |  获取一个缓存对象  |
| [storage](functions/storage.md) |  获取一个储存对象  |
| [config](functions/config.md) |  获取一个配置对象  |
| [cmd](functions/cmd.md) |  新建一个命令对象  |
| [class_name](functions/class_name.md) |  获取类名，将JAVA包式的类名转化为 PHP的标准类名  |
| [table](functions/table.md) |  获取数据表对象，该对象需要在module.json文件中注册过  |
| [session](functions/session.md) |  获取默认Session对象  |
| [module](functions/module.md) |  获取当前文件所在的模块  |
| [email_poster](functions/email_poster.md) |  获取邮件发送 **使用前请设置完成SMTP规则**  |



## 类列表

| 类名 | 说明 |
|------|-----|
|[suda\core\Autoloader](classes/suda/core/Autoloader.md) | 自动加载控制器 |
|[suda\core\System](classes/suda/core/System.md) | 系统类，处理系统报错函数以及程序加载 |
|[suda\core\Debug](classes/suda/core/Debug.md) | 异常日志类 |
|[suda\core\Request](classes/suda/core/Request.md) | 请求描述类，客户端向框架发送请求时会生成此类 |
|[suda\tool\Value](classes/suda/tool/Value.md) | 普通通用值 |
|[suda\core\Config](classes/suda/core/Config.md) | 文件配置类 |
|[suda\tool\ArrayHelper](classes/suda/tool/ArrayHelper.md) | 数组操纵 |
|[suda\core\Storage](classes/suda/core/Storage.md) | 文件存储系统包装类，封装了常用的文件系统函数 |
|[suda\core\Hook](classes/suda/core/Hook.md) | 系统钩子，监听系统内部一些操作并载入一些自定义行为 |
|[suda\core\Locale](classes/suda/core/Locale.md) | I18N 国际化支持 |
|[suda\tool\Json](classes/suda/tool/Json.md) |  |
|[suda\core\Application](classes/suda/core/Application.md) | 应用处理类 |
|[suda\template\Manager](classes/suda/template/Manager.md) | 模板管理类 |
|[suda\template\compiler\suda\Compiler](classes/suda/template/compiler/suda/Compiler.md) | Suda 模板编译器 |
|[suda\tool\Command](classes/suda/tool/Command.md) |  |
|[suda\archive\Connection](classes/suda/archive/Connection.md) | 数据表链接对象 |
|[suda\archive\creator\Field](classes/suda/archive/creator/Field.md) |  |
|[suda\archive\creator\InputValue](classes/suda/archive/creator/InputValue.md) |  |
|[suda\archive\creator\Table](classes/suda/archive/creator/Table.md) |  |
|[suda\archive\RawQuery](classes/suda/archive/RawQuery.md) | 数据库查询方案，提供原始查询方案 |
|[suda\archive\SQLQuery](classes/suda/archive/SQLQuery.md) | 数据库查询方案，简化数据库查 |
|[suda\archive\Table](classes/suda/archive/Table.md) | 数据表抽象对象 |
|[suda\archive\TableInstance](classes/suda/archive/TableInstance.md) |  |
|[suda\core\Cache](classes/suda/core/Cache.md) | 文件缓存 |
|[suda\core\Cookie](classes/suda/core/Cookie.md) | Cookie操作封装类 |
|[suda\core\Exception](classes/suda/core/Exception.md) | 通用系统异常 |
|[suda\core\Query](classes/suda/core/Query.md) | 数据库查询类 |
|[suda\core\Response](classes/suda/core/Response.md) | 网页响应类，用于处理来自服务器的请求 |
|[suda\core\route\Mapping](classes/suda/core/route/Mapping.md) |  |
|[suda\core\Router](classes/suda/core/Router.md) | 路由处理类 |
|[suda\core\Session](classes/suda/core/Session.md) | 会话操纵类 |
|[suda\exception\ApplicationException](classes/suda/exception/ApplicationException.md) |  |
|[suda\exception\ArchiveException](classes/suda/exception/ArchiveException.md) |  |
|[suda\exception\CommandException](classes/suda/exception/CommandException.md) |  |
|[suda\exception\JSONException](classes/suda/exception/JSONException.md) |  |
|[suda\exception\KernelException](classes/suda/exception/KernelException.md) |  |
|[suda\exception\MailException](classes/suda/exception/MailException.md) |  |
|[suda\exception\SQLException](classes/suda/exception/SQLException.md) |  |
|[suda\exception\TableException](classes/suda/exception/TableException.md) |  |
|[suda\mail\Factory](classes/suda/mail/Factory.md) |  |
|[suda\mail\message\Message](classes/suda/mail/message/Message.md) | 文本邮件信息 |
|[suda\mail\message\HTMLMessage](classes/suda/mail/message/HTMLMessage.md) | HTML邮件信息 |
|[suda\mail\sender\MailSender](classes/suda/mail/sender/MailSender.md) | sendmail 邮件发送 |
|[suda\mail\sender\StmpSender](classes/suda/mail/sender/StmpSender.md) | SMTP邮件发送器 |
|[suda\template\compiler\suda\Template](classes/suda/template/compiler/suda/Template.md) |  |
|[suda\template\compiler\suda\TemplateInfo](classes/suda/template/compiler/suda/TemplateInfo.md) |  |
|[suda\tool\ArrayValue](classes/suda/tool/ArrayValue.md) |  |
|[suda\tool\CookieSetter](classes/suda/tool/CookieSetter.md) |  |
|[suda\tool\EchoValue](classes/suda/tool/EchoValue.md) |  |
|[suda\tool\Pinyin](classes/suda/tool/Pinyin.md) | 将中文转换成拼音 |
|[suda\tool\ZipHelper](classes/suda/tool/ZipHelper.md) |  |