# Session::get

> *文件信息* suda\core\session\Session.php: 22~30
## 所属类 

[Session](../Session.md)

## 可见性

abstract  public  
## 说明

该函数暂时无说明

## 参数

 
| 参数名 | 类型 | 默认值 | 说明 |
|--------|-----|-------|-------|
 | name |  string |  | 无 |
 | default |  # Error> htmlspecialchars() expects parameter 1 to be string, array given
  Cause By D:\Server\vhost\atd3.org\suda\script\docme\template\method.md.tpl.php:25
    =>  suda\core\System::uncaughtError(2,htmlspecialchars() expects parameter 1 to be string, array given,D:\Server\vhost\atd3.org\suda\script\docme\template\method.md.tpl.php,25,{"param":{"default":"null"},"name":"default"})
    => D:\Server\vhost\atd3.org\suda\script\docme\template\method.md.tpl.php:25 htmlspecialchars(["\u65e0"],59)
    => D:\Server\vhost\atd3.org\suda\system\src\suda\template\compiler\suda\Template.php:137 Class860d7316e63cf22173dfe0b15c6a4979->_render_template()
    => D:\Server\vhost\atd3.org\suda\system\src\suda\template\compiler\suda\Template.php:101 suda\template\compiler\suda\Template->echo()
    => D:\Server\vhost\atd3.org\suda\script\docme\src\ExportTemplate.php:45 suda\template\compiler\suda\Template->getRenderedString()
    => D:\Server\vhost\atd3.org\suda\script\docme\src\ClassExport.php:103 docme\ExportTemplate->export(D:\Server\vhost\atd3.org\suda\script/../docs/classes/suda\core\session\Session/get.md)
    => D:\Server\vhost\atd3.org\suda\script\docme\src\ClassExport.php:65 docme\ClassExport->exportMethod(class ReflectionMethod,{"description":"Session \u63a5\u53e3","className":"Session","classFullName":"suda\\core\\session\\Session","classDoc":"Session \u63a5\u53e3","constants":[],"fileName":"suda\\core\\session\\Session.php","lineStart":22,"lineEnd":30,"properties":[]},D:\Server\vhost\atd3.org\suda\script/../docs/classes/suda\core\session\Session)
    => D:\Server\vhost\atd3.org\suda\script\docme\src\Docme.php:97 docme\ClassExport->export(D:\Server\vhost\atd3.org\suda\script/../docs/classes)
    => D:\Server\vhost\atd3.org\suda\script\docme.php:27 docme\Docme->export(D:\Server\vhost\atd3.org\suda\script/../docs)
 | null | 无 |
## 返回值
返回值类型不定
## 例子

example