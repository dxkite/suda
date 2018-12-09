# Template::get
创建模板获取值
> *文件信息* suda\template\compiler\suda\Template.php: 29~345
## 所属类 

[Template](../Template.md)

## 可见性

  public  
## 说明

该函数暂时无说明

## 参数

 
| 参数名 | 类型 | 默认值 | 说明 |
|--------|-----|-------|-------|
 | name |  string | 无 | 无 |
 | default |  # Error> htmlspecialchars() expects parameter 1 to be string, array given
  Cause By D:\Server\vhost\atd3.org\suda\script\docme\template\method.md.tpl.php:25
    =>  suda\core\System::uncaughtError(2,htmlspecialchars() expects parameter 1 to be string, array given,D:\Server\vhost\atd3.org\suda\script\docme\template\method.md.tpl.php,25,{"param":{"default":"null"},"name":"default"})
    => D:\Server\vhost\atd3.org\suda\script\docme\template\method.md.tpl.php:25 htmlspecialchars(["\u65e0"],59)
    => D:\Server\vhost\atd3.org\suda\system\src\suda\template\compiler\suda\Template.php:137 Class860d7316e63cf22173dfe0b15c6a4979->_render_template()
    => D:\Server\vhost\atd3.org\suda\system\src\suda\template\compiler\suda\Template.php:101 suda\template\compiler\suda\Template->echo()
    => D:\Server\vhost\atd3.org\suda\script\docme\src\ExportTemplate.php:45 suda\template\compiler\suda\Template->getRenderedString()
    => D:\Server\vhost\atd3.org\suda\script\docme\src\ClassExport.php:103 docme\ExportTemplate->export(D:\Server\vhost\atd3.org\suda\script/../docs/classes/suda\template\compiler\suda\Template/get.md)
    => D:\Server\vhost\atd3.org\suda\script\docme\src\ClassExport.php:65 docme\ClassExport->exportMethod(class ReflectionMethod,{"description":false,"className":"Template","classFullName":"suda\\template\\compiler\\suda\\Template","classDoc":false,"constants":[],"fileName":"suda\\template\\compiler\\suda\\Template.php","lineStart":29,"lineEnd":345,"properties":{"value":{"visibility":"protected","static":"","docs":"\u6a21\u677f\u7684\u503c"},"response":{"visibility":"protected","static":"","docs":"\u6a21\u677f\u6240\u5c5e\u4e8e\u7684\u54cd\u5e94"},"name":{"visibility":"protected","static":"","docs":false},"parent":{"visibility":"protected","static":"","docs":false},"hooks":{"visibility":"protected","static":"","docs":false},"module":{"visibility":"protected","static":"","docs":false},"source":{"visibility":"protected","static":"","docs":false},"render":{"visibility":"protected","static":"static","docs":false},"nonce":{"visibility":"protected","static":"static","docs":false},"extend":{"visibility":"protected","static":"","docs":false}}},D:\Server\vhost\atd3.org\suda\script/../docs/classes/suda\template\compiler\suda\Template)
    => D:\Server\vhost\atd3.org\suda\script\docme\src\Docme.php:90 docme\ClassExport->export(D:\Server\vhost\atd3.org\suda\script/../docs/classes)
    => D:\Server\vhost\atd3.org\suda\script\docme.php:27 docme\Docme->export(D:\Server\vhost\atd3.org\suda\script/../docs)
 | null | 无 |
## 返回值
返回值类型不定
## 例子

example