# ArrayHelper::get
获取数组元素
> *文件信息* suda\tool\ArrayHelper.php: 23~172
## 所属类 

[ArrayHelper](../ArrayHelper.md)

## 可见性

  public  static
## 说明


设置值， 获取值，导出成文件

## 参数

| 参数名 | 类型 | 默认值 | 说明 |
|--------|-----|-------|-------|
| array |  array | 无 | 无 |
| name |  string | 无 | 无 |
| def |  # Error> htmlspecialchars() expects parameter 1 to be string, array given
  Cause By D:\Server\vhost\atd3.org\suda\script\docme\template\method.md.tpl:25
    =>  suda\core\System::uncaughtError(2,htmlspecialchars() expects parameter 1 to be string, array given,D:\Server\vhost\atd3.org\suda\script\docme\template\method.md.tpl,25,{"param":{"default":"null"},"name":"def"})
    => D:\Server\vhost\atd3.org\suda\script\docme\template\method.md.tpl:25 htmlspecialchars(["\u65e0"])
    => D:\Server\vhost\atd3.org\suda\system\src\suda\template\compiler\suda\Template.php:127 Class860d7316e63cf22173dfe0b15c6a4979->_render_template()
    => D:\Server\vhost\atd3.org\suda\system\src\suda\template\compiler\suda\Template.php:91 suda\template\compiler\suda\Template->echo()
    => D:\Server\vhost\atd3.org\suda\script\docme\src\ExportTemplate.php:45 suda\template\compiler\suda\Template->getRenderedString()
    => D:\Server\vhost\atd3.org\suda\script\docme\src\ClassExport.php:103 docme\ExportTemplate->export(D:\Server\vhost\atd3.org\suda\script/../docs/classes/suda\tool\ArrayHelper/get.md)
    => D:\Server\vhost\atd3.org\suda\script\docme\src\ClassExport.php:65 docme\ClassExport->exportMethod(class ReflectionMethod,{"description":"\u6570\u7ec4\u64cd\u7eb5","document":"\r\n\u8bbe\u7f6e\u503c\uff0c \u83b7\u53d6\u503c\uff0c\u5bfc\u51fa\u6210\u6587\u4ef6","className":"ArrayHelper","classFullName":"suda\\tool\\ArrayHelper","classDoc":"\u6570\u7ec4\u64cd\u7eb5","constants":[],"fileName":"suda\\tool\\ArrayHelper.php","lineStart":23,"lineEnd":172,"properties":[]},D:\Server\vhost\atd3.org\suda\script/../docs/classes/suda\tool\ArrayHelper)
    => D:\Server\vhost\atd3.org\suda\script\docme\src\Docme.php:90 docme\ClassExport->export(D:\Server\vhost\atd3.org\suda\script/../docs/classes)
    => D:\Server\vhost\atd3.org\suda\script\docme.php:30 docme\Docme->export(D:\Server\vhost\atd3.org\suda\script/../docs)
 | null | 无 |

## 返回值
类型：mixed
 查询的值

## 例子

array_get_value('a.b.c.d',$arr);
返回 $arr['a']['b']['c']['d'];