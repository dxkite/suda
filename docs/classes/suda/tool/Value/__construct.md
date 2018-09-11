# Value::__construct
Value constructor.
> *文件信息* suda\tool\Value.php: 26~135
## 所属类 

[Value](../Value.md)

## 可见性

  public  
## 说明


通用指可以使用迭代器和JSON化成字符串
并且包含魔术变量用于处理其值

@package suda\tool

## 参数

| 参数名 | 类型 | 默认值 | 说明 |
|--------|-----|-------|-------|
| var |  # Error> htmlspecialchars() expects parameter 1 to be string, array given
  Cause By D:\Server\vhost\atd3.org\suda\script\docme\template\method.md.tpl:25
    =>  suda\core\System::uncaughtError(2,htmlspecialchars() expects parameter 1 to be string, array given,D:\Server\vhost\atd3.org\suda\script\docme\template\method.md.tpl,25,{"param":{"default":"Array"},"name":"var"})
    => D:\Server\vhost\atd3.org\suda\script\docme\template\method.md.tpl:25 htmlspecialchars(["\u65e0"])
    => D:\Server\vhost\atd3.org\suda\system\src\suda\template\compiler\suda\Template.php:127 Class860d7316e63cf22173dfe0b15c6a4979->_render_template()
    => D:\Server\vhost\atd3.org\suda\system\src\suda\template\compiler\suda\Template.php:91 suda\template\compiler\suda\Template->echo()
    => D:\Server\vhost\atd3.org\suda\script\docme\src\ExportTemplate.php:45 suda\template\compiler\suda\Template->getRenderedString()
    => D:\Server\vhost\atd3.org\suda\script\docme\src\ClassExport.php:103 docme\ExportTemplate->export(D:\Server\vhost\atd3.org\suda\script/../docs/classes/suda\tool\Value/__construct.md)
    => D:\Server\vhost\atd3.org\suda\script\docme\src\ClassExport.php:65 docme\ClassExport->exportMethod(class ReflectionMethod,{"description":"\u666e\u901a\u901a\u7528\u503c","document":"\r\n\u901a\u7528\u6307\u53ef\u4ee5\u4f7f\u7528\u8fed\u4ee3\u5668\u548cJSON\u5316\u6210\u5b57\u7b26\u4e32\r\n\u5e76\u4e14\u5305\u542b\u9b54\u672f\u53d8\u91cf\u7528\u4e8e\u5904\u7406\u5176\u503c\r\n\r\n@package suda\\tool","className":"Value","classFullName":"suda\\tool\\Value","classDoc":"\u666e\u901a\u901a\u7528\u503c","constants":[],"fileName":"suda\\tool\\Value.php","lineStart":26,"lineEnd":135,"properties":{"var":{"visibility":"protected","static":"","docs":"@var"}}},D:\Server\vhost\atd3.org\suda\script/../docs/classes/suda\tool\Value)
    => D:\Server\vhost\atd3.org\suda\script\docme\src\Docme.php:90 docme\ClassExport->export(D:\Server\vhost\atd3.org\suda\script/../docs/classes)
    => D:\Server\vhost\atd3.org\suda\script\docme.php:30 docme\Docme->export(D:\Server\vhost\atd3.org\suda\script/../docs)
 | Array | 无 |

## 返回值
返回值类型不定

## 例子

example