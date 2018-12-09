# FileCache::get
获取值
> *文件信息* suda\core\cache\FileCache.php: 28~168
## 所属类 

[FileCache](../FileCache.md)

## 可见性

  public  
## 说明


由于访问数据库的效率远远低于访问文件的效率，所以我添加了一个文件缓存类，
你可以把常用的数据和更改很少的数据查询数据库以后缓存到文件里面，用来加快页面加载速度。

## 参数

 
| 参数名 | 类型 | 默认值 | 说明 |
|--------|-----|-------|-------|
 | name |  string | 无 |  名 |
 | defalut |  # Error> htmlspecialchars() expects parameter 1 to be string, array given
  Cause By D:\Server\vhost\atd3.org\suda\script\docme\template\method.md.tpl.php:25
    =>  suda\core\System::uncaughtError(2,htmlspecialchars() expects parameter 1 to be string, array given,D:\Server\vhost\atd3.org\suda\script\docme\template\method.md.tpl.php,25,{"param":{"default":"null"},"name":"defalut"})
    => D:\Server\vhost\atd3.org\suda\script\docme\template\method.md.tpl.php:25 htmlspecialchars(["\u65e0"],59)
    => D:\Server\vhost\atd3.org\suda\system\src\suda\template\compiler\suda\Template.php:137 Class860d7316e63cf22173dfe0b15c6a4979->_render_template()
    => D:\Server\vhost\atd3.org\suda\system\src\suda\template\compiler\suda\Template.php:101 suda\template\compiler\suda\Template->echo()
    => D:\Server\vhost\atd3.org\suda\script\docme\src\ExportTemplate.php:45 suda\template\compiler\suda\Template->getRenderedString()
    => D:\Server\vhost\atd3.org\suda\script\docme\src\ClassExport.php:103 docme\ExportTemplate->export(D:\Server\vhost\atd3.org\suda\script/../docs/classes/suda\core\cache\FileCache/get.md)
    => D:\Server\vhost\atd3.org\suda\script\docme\src\ClassExport.php:65 docme\ClassExport->exportMethod(class ReflectionMethod,{"description":"\u6587\u4ef6\u7f13\u5b58","document":"\r\n\u7531\u4e8e\u8bbf\u95ee\u6570\u636e\u5e93\u7684\u6548\u7387\u8fdc\u8fdc\u4f4e\u4e8e\u8bbf\u95ee\u6587\u4ef6\u7684\u6548\u7387\uff0c\u6240\u4ee5\u6211\u6dfb\u52a0\u4e86\u4e00\u4e2a\u6587\u4ef6\u7f13\u5b58\u7c7b\uff0c\r\n\u4f60\u53ef\u4ee5\u628a\u5e38\u7528\u7684\u6570\u636e\u548c\u66f4\u6539\u5f88\u5c11\u7684\u6570\u636e\u67e5\u8be2\u6570\u636e\u5e93\u4ee5\u540e\u7f13\u5b58\u5230\u6587\u4ef6\u91cc\u9762\uff0c\u7528\u6765\u52a0\u5feb\u9875\u9762\u52a0\u8f7d\u901f\u5ea6\u3002","className":"FileCache","classFullName":"suda\\core\\cache\\FileCache","classDoc":"\u6587\u4ef6\u7f13\u5b58","constants":{"CACHE_DEFAULT":86400},"fileName":"suda\\core\\cache\\FileCache.php","lineStart":28,"lineEnd":168,"properties":{"cache":{"visibility":"public","static":"static","docs":false},"storage":{"visibility":"public","static":"static","docs":false},"intance":{"visibility":"protected","static":"static","docs":false}}},D:\Server\vhost\atd3.org\suda\script/../docs/classes/suda\core\cache\FileCache)
    => D:\Server\vhost\atd3.org\suda\script\docme\src\Docme.php:90 docme\ClassExport->export(D:\Server\vhost\atd3.org\suda\script/../docs/classes)
    => D:\Server\vhost\atd3.org\suda\script\docme.php:27 docme\Docme->export(D:\Server\vhost\atd3.org\suda\script/../docs)
 | null | 无 |
## 返回值
 
类型：mixed|null
无
## 例子

example