# SQLStatementPrepare::count

> *文件信息* suda\archive\SQLStatementPrepare.php: 24~324
## 所属类 

[SQLStatementPrepare](../SQLStatementPrepare.md)

## 可见性

  public  
## 说明



## 参数

| 参数名 | 类型 | 默认值 | 说明 |
|--------|-----|-------|-------|
| table |  string | 无 | 无 |
| where |  # Error> htmlspecialchars() expects parameter 1 to be string, array given
  Cause By D:\Server\vhost\atd3.org\suda\script\docme\template\method.md.tpl.php:25
    =>  suda\core\System::uncaughtError(2,htmlspecialchars() expects parameter 1 to be string, array given,D:\Server\vhost\atd3.org\suda\script\docme\template\method.md.tpl.php,25,{"param":{"default":"1"},"name":"where"})
    => D:\Server\vhost\atd3.org\suda\script\docme\template\method.md.tpl.php:25 htmlspecialchars(["\u65e0"])
    => D:\Server\vhost\atd3.org\suda\system\src\suda\template\compiler\suda\Template.php:130 Class860d7316e63cf22173dfe0b15c6a4979->_render_template()
    => D:\Server\vhost\atd3.org\suda\system\src\suda\template\compiler\suda\Template.php:94 suda\template\compiler\suda\Template->echo()
    => D:\Server\vhost\atd3.org\suda\script\docme\src\ExportTemplate.php:45 suda\template\compiler\suda\Template->getRenderedString()
    => D:\Server\vhost\atd3.org\suda\script\docme\src\ClassExport.php:103 docme\ExportTemplate->export(D:\Server\vhost\atd3.org\suda\script/../docs/classes/suda\archive\SQLStatementPrepare/count.md)
    => D:\Server\vhost\atd3.org\suda\script\docme\src\ClassExport.php:65 docme\ClassExport->exportMethod(class ReflectionMethod,{"description":"\u6570\u636e\u5e93\u67e5\u8be2\u8bed\u53e5\u63a5\u53e3","document":"","className":"SQLStatementPrepare","classFullName":"suda\\archive\\SQLStatementPrepare","classDoc":"\u6570\u636e\u5e93\u67e5\u8be2\u8bed\u53e5\u63a5\u53e3","constants":[],"fileName":"suda\\archive\\SQLStatementPrepare.php","lineStart":24,"lineEnd":324,"properties":{"connection":{"visibility":"protected","static":"","docs":false},"query":{"visibility":"protected","static":"","docs":false}}},D:\Server\vhost\atd3.org\suda\script/../docs/classes/suda\archive\SQLStatementPrepare)
    => D:\Server\vhost\atd3.org\suda\script\docme\src\Docme.php:90 docme\ClassExport->export(D:\Server\vhost\atd3.org\suda\script/../docs/classes)
    => D:\Server\vhost\atd3.org\suda\script\docme.php:27 docme\Docme->export(D:\Server\vhost\atd3.org\suda\script/../docs)
 | 1 | 无 |
| binds |  array | Array | 无 |

## 返回值
返回值类型不定

## 例子

example