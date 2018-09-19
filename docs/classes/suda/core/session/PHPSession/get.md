# PHPSession::get

> *文件信息* suda\core\session\PHPSession.php: 25~84
## 所属类 

[PHPSession](../PHPSession.md)

## 可见性

  public  
## 说明

控制PHP全局会话，

## 参数

| 参数名 | 类型 | 默认值 | 说明 |
|--------|-----|-------|-------|
| name |  string |  | 无 |
| default |  # Error> htmlspecialchars() expects parameter 1 to be string, array given
  Cause By D:\Server\vhost\atd3.org\suda\script\docme\template\method.md.tpl:25
    =>  suda\core\System::uncaughtError(2,htmlspecialchars() expects parameter 1 to be string, array given,D:\Server\vhost\atd3.org\suda\script\docme\template\method.md.tpl,25,{"param":{"default":"null"},"name":"default"})
    => D:\Server\vhost\atd3.org\suda\script\docme\template\method.md.tpl:25 htmlspecialchars(["\u65e0"])
    => D:\Server\vhost\atd3.org\suda\system\src\suda\template\compiler\suda\Template.php:127 Class860d7316e63cf22173dfe0b15c6a4979->_render_template()
    => D:\Server\vhost\atd3.org\suda\system\src\suda\template\compiler\suda\Template.php:91 suda\template\compiler\suda\Template->echo()
    => D:\Server\vhost\atd3.org\suda\script\docme\src\ExportTemplate.php:45 suda\template\compiler\suda\Template->getRenderedString()
    => D:\Server\vhost\atd3.org\suda\script\docme\src\ClassExport.php:103 docme\ExportTemplate->export(D:\Server\vhost\atd3.org\suda\script/../docs/classes/suda\core\session\PHPSession/get.md)
    => D:\Server\vhost\atd3.org\suda\script\docme\src\ClassExport.php:65 docme\ClassExport->exportMethod(class ReflectionMethod,{"description":"\u4f1a\u8bdd\u64cd\u7eb5\u7c7b","document":"\u63a7\u5236PHP\u5168\u5c40\u4f1a\u8bdd\uff0c","className":"PHPSession","classFullName":"suda\\core\\session\\PHPSession","classDoc":"\u4f1a\u8bdd\u64cd\u7eb5\u7c7b","constants":[],"fileName":"suda\\core\\session\\PHPSession.php","lineStart":25,"lineEnd":84,"properties":{"instance":{"visibility":"protected","static":"static","docs":false}}},D:\Server\vhost\atd3.org\suda\script/../docs/classes/suda\core\session\PHPSession)
    => D:\Server\vhost\atd3.org\suda\script\docme\src\Docme.php:90 docme\ClassExport->export(D:\Server\vhost\atd3.org\suda\script/../docs/classes)
    => D:\Server\vhost\atd3.org\suda\script\docme.php:30 docme\Docme->export(D:\Server\vhost\atd3.org\suda\script/../docs)
 | null | 无 |

## 返回值
返回值类型不定

## 例子

example