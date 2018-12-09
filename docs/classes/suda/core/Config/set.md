# Config::set

> *文件信息* suda\core\Config.php: 24~150
## 所属类 

[Config](../Config.md)

## 可见性

  public  static
## 说明

该函数暂时无说明

## 参数

 
| 参数名 | 类型 | 默认值 | 说明 |
|--------|-----|-------|-------|
 | name |  string | 无 | 无 |
 | combine |  # Error> htmlspecialchars() expects parameter 1 to be string, array given
  Cause By D:\Server\vhost\atd3.org\suda\script\docme\template\method.md.tpl.php:25
    =>  suda\core\System::uncaughtError(2,htmlspecialchars() expects parameter 1 to be string, array given,D:\Server\vhost\atd3.org\suda\script\docme\template\method.md.tpl.php,25,{"param":{"default":"null"},"name":"combine"})
    => D:\Server\vhost\atd3.org\suda\script\docme\template\method.md.tpl.php:25 htmlspecialchars(["\u65e0"],59)
    => D:\Server\vhost\atd3.org\suda\system\src\suda\template\compiler\suda\Template.php:137 Class860d7316e63cf22173dfe0b15c6a4979->_render_template()
    => D:\Server\vhost\atd3.org\suda\system\src\suda\template\compiler\suda\Template.php:101 suda\template\compiler\suda\Template->echo()
    => D:\Server\vhost\atd3.org\suda\script\docme\src\ExportTemplate.php:45 suda\template\compiler\suda\Template->getRenderedString()
    => D:\Server\vhost\atd3.org\suda\script\docme\src\ClassExport.php:103 docme\ExportTemplate->export(D:\Server\vhost\atd3.org\suda\script/../docs/classes/suda\core\Config/set.md)
    => D:\Server\vhost\atd3.org\suda\script\docme\src\ClassExport.php:65 docme\ClassExport->exportMethod(class ReflectionMethod,{"description":"\u6587\u4ef6\u914d\u7f6e\u7c7b","className":"Config","classFullName":"suda\\core\\Config","classDoc":"\u6587\u4ef6\u914d\u7f6e\u7c7b","constants":[],"fileName":"suda\\core\\Config.php","lineStart":24,"lineEnd":150,"properties":{"config":{"visibility":"public","static":"static","docs":false}}},D:\Server\vhost\atd3.org\suda\script/../docs/classes/suda\core\Config)
    => D:\Server\vhost\atd3.org\suda\script\docme\src\Docme.php:90 docme\ClassExport->export(D:\Server\vhost\atd3.org\suda\script/../docs/classes)
    => D:\Server\vhost\atd3.org\suda\script\docme.php:27 docme\Docme->export(D:\Server\vhost\atd3.org\suda\script/../docs)
 | null | 无 |
## 返回值
返回值类型不定
## 例子

example