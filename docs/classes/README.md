# 文档清单

> **注：** 文档由程序自动生成

- suda 2.0.3 
- 2018-12-09 04:14:35


## 类列表

| 类名 | 说明 |
|------|-----|
|[suda\core\Autoloader](suda/core/Autoloader.md) | 自动加载控制器 |
|[suda\core\System](suda/core/System.md) | 系统类，处理系统报错函数以及程序加载 |
|[suda\core\Debug](suda/core/Debug.md) | 异常日志类 |
|[suda\core\Storage](suda/core/Storage.md) | 文件存储系统包装类，封装了常用的文件系统函数 |
|[suda\core\storage\FileStorage](suda/core/storage/FileStorage.md) | 文件存储系统包装类 |
|[suda\core\Request](suda/core/Request.md) | 请求描述类，客户端向框架发送请求时会生成此类 |
|[suda\core\Config](suda/core/Config.md) | 文件配置类 |
|[suda\tool\ArrayHelper](suda/tool/ArrayHelper.md) | 数组操纵 |
|[suda\core\Hook](suda/core/Hook.md) | 系统钩子，监听系统内部一些操作并载入一些自定义行为 |
|[suda\core\Locale](suda/core/Locale.md) | I18N 国际化支持 |
|[suda\core\storage\iterator\PathPregFilterIterator](suda/core/storage/iterator/PathPregFilterIterator.md) | 路径正则迭代器 |
|[suda\archive\Connection](suda/archive/Connection.md) | 数据表链接对象 |
|[suda\archive\creator\Field](suda/archive/creator/Field.md) | 数据表字段创建工具 |
|[suda\archive\creator\InputValue](suda/archive/creator/InputValue.md) | 数据输入值 |
|[suda\archive\creator\Table](suda/archive/creator/Table.md) | 数据表结构构建类 |
|[suda\archive\RawQuery](suda/archive/RawQuery.md) | 数据库查询方案，提供原始查询方案 |
|[suda\archive\SQLStatementPrepare](suda/archive/SQLStatementPrepare.md) | 数据库查询语句接口 |
|[suda\archive\SQLQuery](suda/archive/SQLQuery.md) | 数据库查询方案，简化数据库查 |
|[suda\archive\TableAccess](suda/archive/TableAccess.md) | 表创建器 |
|[suda\archive\Table](suda/archive/Table.md) | 数据表抽象对象 |
|[suda\core\Application](suda/core/Application.md) | 应用处理类 |
|[suda\core\cache\FileCache](suda/core/cache/FileCache.md) | 文件缓存 |
|[suda\core\Cache](suda/core/Cache.md) | 缓存系统 |
|[suda\core\Cookie](suda/core/Cookie.md) | Cookie操作封装类 |
|[suda\core\Exception](suda/core/Exception.md) | 通用系统异常 |
|[suda\core\Query](suda/core/Query.md) | 数据库查询类 |
|[suda\core\Response](suda/core/Response.md) | 网页响应类，用于处理来自服务器的请求 |
|[suda\core\route\Mapping](suda/core/route/Mapping.md) |  |
|[suda\core\Router](suda/core/Router.md) | 路由处理类 |
|[suda\core\session\PHPSession](suda/core/session/PHPSession.md) | 会话操纵类 |
|[suda\core\Session](suda/core/Session.md) | 会话操纵类 |
|[suda\exception\ApplicationException](suda/exception/ApplicationException.md) |  |
|[suda\exception\ArchiveException](suda/exception/ArchiveException.md) |  |
|[suda\exception\CommandException](suda/exception/CommandException.md) |  |
|[suda\exception\JSONException](suda/exception/JSONException.md) |  |
|[suda\exception\KernelException](suda/exception/KernelException.md) |  |
|[suda\exception\MailException](suda/exception/MailException.md) |  |
|[suda\exception\PregException](suda/exception/PregException.md) |  |
|[suda\exception\SQLException](suda/exception/SQLException.md) |  |
|[suda\exception\TableException](suda/exception/TableException.md) |  |
|[suda\mail\Factory](suda/mail/Factory.md) |  |
|[suda\mail\message\Message](suda/mail/message/Message.md) | 文本邮件信息 |
|[suda\mail\message\HTMLMessage](suda/mail/message/HTMLMessage.md) | HTML邮件信息 |
|[suda\mail\sender\MailSender](suda/mail/sender/MailSender.md) | sendmail 邮件发送 |
|[suda\mail\sender\StmpSender](suda/mail/sender/StmpSender.md) | SMTP邮件发送器 |
|[suda\template\compiler\suda\Compiler](suda/template/compiler/suda/Compiler.md) | Suda 模板编译器 |
|[suda\template\compiler\suda\Template](suda/template/compiler/suda/Template.md) |  |
|[suda\template\compiler\suda\TemplateInfo](suda/template/compiler/suda/TemplateInfo.md) | 获取模板信息类 |
|[suda\template\iterator\RecursiveTemplateIterator](suda/template/iterator/RecursiveTemplateIterator.md) |  |
|[suda\template\Manager](suda/template/Manager.md) | 模板管理类 |
|[suda\tool\Command](suda/tool/Command.md) | 可执行命令表达式 |
|[suda\tool\CookieSetter](suda/tool/CookieSetter.md) |  |
|[suda\tool\Security](suda/tool/Security.md) | 安全辅助工具 |
|[suda\tool\ZipHelper](suda/tool/ZipHelper.md) |  |
|[suda\core\storage\Storage](suda/core/storage/Storage.md) | 存储系统 |
|[suda\archive\SQLStatement](suda/archive/SQLStatement.md) | 数据库查询语句接口 |
|[suda\core\cache\Cache](suda/core/cache/Cache.md) | 缓存接口 |
|[suda\core\session\Session](suda/core/session/Session.md) | Session 接口 |
|[suda\mail\sender\Sender](suda/mail/sender/Sender.md) |  |
|[suda\template\Compiler](suda/template/Compiler.md) | 编译器接口 |
|[suda\template\Template](suda/template/Template.md) |  |