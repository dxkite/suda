# ArrayValue::__construct
Value constructor.
> *文件信息* suda\tool\ArrayValue.php: 18~25
## 所属类 

[ArrayValue](../ArrayValue.md)

## 可见性

  public  
## 说明

@param $var

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
    => D:\Server\vhost\atd3.org\suda\script\docme\src\ClassExport.php:103 docme\ExportTemplate->export(D:\Server\vhost\atd3.org\suda\script/../docs/classes/suda\tool\ArrayValue/__construct.md)
    => D:\Server\vhost\atd3.org\suda\script\docme\src\ClassExport.php:65 docme\ClassExport->exportMethod(class ReflectionMethod,{"description":false,"className":"ArrayValue","classFullName":"suda\\tool\\ArrayValue","classDoc":false,"constants":[],"fileName":"suda\\tool\\ArrayValue.php","lineStart":18,"lineEnd":25,"properties":{"var":{"visibility":"protected","static":"","docs":"@var"}}},D:\Server\vhost\atd3.org\suda\script/../docs/classes/suda\tool\ArrayValue)
    => D:\Server\vhost\atd3.org\suda\script\docme\src\Docme.php:90 docme\ClassExport->export(D:\Server\vhost\atd3.org\suda\script/../docs/classes)
    => D:\Server\vhost\atd3.org\suda\script\docme.php:30 docme\Docme->export(D:\Server\vhost\atd3.org\suda\script/../docs)
 | Array | 无 |

## 返回值
返回值类型不定

## 例子

example