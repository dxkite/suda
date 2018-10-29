# Cookie::get
获取Cookie的值
> *文件信息* suda\core\Cookie.php: 24~97
## 所属类 

[Cookie](../Cookie.md)

## 可见性

  public  static
## 说明

用于操作Cookie

## 参数

| 参数名 | 类型 | 默认值 | 说明 |
|--------|-----|-------|-------|
| name |  string | 无 | 无 |
| default |  # Error> htmlspecialchars() expects parameter 1 to be string, array given
  Cause By D:\Server\vhost\atd3.org\suda\script\docme\template\method.md.tpl.php:25
    =>  suda\core\System::uncaughtError(2,htmlspecialchars() expects parameter 1 to be string, array given,D:\Server\vhost\atd3.org\suda\script\docme\template\method.md.tpl.php,25,{"param":{"default":""},"name":"default"})
    => D:\Server\vhost\atd3.org\suda\script\docme\template\method.md.tpl.php:25 htmlspecialchars(["\u65e0"])
    => D:\Server\vhost\atd3.org\suda\system\src\suda\template\compiler\suda\Template.php:130 Class860d7316e63cf22173dfe0b15c6a4979->_render_template()
    => D:\Server\vhost\atd3.org\suda\system\src\suda\template\compiler\suda\Template.php:94 suda\template\compiler\suda\Template->echo()
    => D:\Server\vhost\atd3.org\suda\script\docme\src\ExportTemplate.php:45 suda\template\compiler\suda\Template->getRenderedString()
    => D:\Server\vhost\atd3.org\suda\script\docme\src\ClassExport.php:103 docme\ExportTemplate->export(D:\Server\vhost\atd3.org\suda\script/../docs/classes/suda\core\Cookie/get.md)
    => D:\Server\vhost\atd3.org\suda\script\docme\src\ClassExport.php:65 docme\ClassExport->exportMethod(class ReflectionMethod,{"description":"Cookie\u64cd\u4f5c\u5c01\u88c5\u7c7b","document":"\u7528\u4e8e\u64cd\u4f5cCookie","className":"Cookie","classFullName":"suda\\core\\Cookie","classDoc":"Cookie\u64cd\u4f5c\u5c01\u88c5\u7c7b","constants":[],"fileName":"suda\\core\\Cookie.php","lineStart":24,"lineEnd":97,"properties":{"values":{"visibility":"public","static":"static","docs":false}}},D:\Server\vhost\atd3.org\suda\script/../docs/classes/suda\core\Cookie)
    => D:\Server\vhost\atd3.org\suda\script\docme\src\Docme.php:90 docme\ClassExport->export(D:\Server\vhost\atd3.org\suda\script/../docs/classes)
    => D:\Server\vhost\atd3.org\suda\script\docme.php:27 docme\Docme->export(D:\Server\vhost\atd3.org\suda\script/../docs)
 |  | 无 |

## 返回值
类型：string
 cookie的值

## 例子

example